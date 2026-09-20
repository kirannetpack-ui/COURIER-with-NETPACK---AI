<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use App\Models\DomesticShipment;
use App\Models\Order;
use App\Models\MAWB;
use App\Models\PickupRequest;
use App\Models\ShipmentAssignment;
use App\Models\TrackingLocation;
use App\Services\ShipmentScanService;
use App\Services\AutomatedTrackingService;
use App\Services\CarrierTrackingSyncService;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
    public function __construct(
        private readonly ShipmentScanService $scanService,
        private readonly AutomatedTrackingService $automatedTracking,
        private readonly CarrierTrackingSyncService $carrierSyncService
    ) {
        $this->middleware('auth')->only(['getLiveLocation', 'getOrderLiveLocation', 'updateLocation', 'updateStatus', 'syncCarrier']);
    }

    /**
     * Show tracking lookup page
     */
    public function lookup()
    {
        return view('tracking.lookup');
    }

    /**
     * Search for tracking number
     */
    public function search(Request $request)
    {
        $trackingNumber = $request->get('tracking');
        
        if ($trackingNumber) {
            return redirect()->route('tracking.show', trim($trackingNumber));
        }
        
        return redirect()->route('tracking.page');
    }

    /**
     * Show tracking details for a shipment (Universal Multi-Identifier Search)
     */
    public function show($trackingNumber)
    {
        $rawNumber = trim($trackingNumber);
        $trackingNumberUpper = strtoupper($rawNumber);
        $cleanNumber = strtoupper(preg_replace('/[^A-Z0-9]/', '', $rawNumber));

        // 1. Try to find in Shipment table (by tracking_number, hawb_number, or last_mile_tracking_number)
        $shipment = Shipment::where('tracking_number', $trackingNumberUpper)
            ->orWhere('hawb_number', $trackingNumberUpper)
            ->orWhere('last_mile_tracking_number', $rawNumber)
            ->orWhere('last_mile_tracking_number', $trackingNumberUpper)
            ->orWhereRaw("REPLACE(REPLACE(tracking_number, '-', ''), ' ', '') = ?", [$cleanNumber])
            ->orWhereRaw("REPLACE(REPLACE(hawb_number, '-', ''), ' ', '') = ?", [$cleanNumber])
            ->with(['hub', 'currentAgency', 'lastMileCarrier', 'mawb', 'issues'])
            ->first();

        // 2. Check if entered code matches a Master Air Waybill (MAWB)
        if (!$shipment) {
            $mawb = MAWB::where('mawb_number', $rawNumber)
                ->orWhere('mawb_number', $trackingNumberUpper)
                ->first();
            if ($mawb) {
                $shipment = Shipment::where('mawb_id', $mawb->id)
                    ->orWhere('mawb_number', $mawb->mawb_number)
                    ->latest()
                    ->with(['hub', 'currentAgency', 'lastMileCarrier', 'mawb', 'issues'])
                    ->first();
            }
        }

        if (!$shipment) {
            // 3. Try domestic shipments
            $domesticShipment = DomesticShipment::where('tracking_number', $trackingNumberUpper)
                ->orWhereRaw("REPLACE(REPLACE(tracking_number, '-', ''), ' ', '') = ?", [$cleanNumber])
                ->with(['trackingEvents', 'domesticRate'])
                ->first();
            if ($domesticShipment) {
                return view('tracking.domestic', ['shipment' => $domesticShipment]);
            }

            // 4. E-commerce and rider deliveries (by tracking_number OR order_number)
            $order = Order::where('tracking_number', $trackingNumberUpper)
                ->orWhere('order_number', $trackingNumberUpper)
                ->orWhereRaw("REPLACE(REPLACE(tracking_number, '-', ''), ' ', '') = ?", [$cleanNumber])
                ->with('rider')
                ->first();
            if ($order) {
                $canViewLive = $this->canViewOrderLiveLocation(request()->user(), $order);

                return view('tracking.order', compact('order', 'canViewLive'));
            }

            // 5. Pickup Request lookup
            $pickup = PickupRequest::where('tracking_number', $trackingNumberUpper)
                ->orWhere('order_reference', $rawNumber)
                ->first();
            if ($pickup) {
                if ($pickup->order_id) {
                    $order = Order::find($pickup->order_id);
                    if ($order) {
                        $canViewLive = $this->canViewOrderLiveLocation(request()->user(), $order);
                        return view('tracking.order', compact('order', 'canViewLive'));
                    }
                }
                return view('domestic.pickup.show', ['pickupRequest' => $pickup]);
            }

            // 6. Direct Rider & Multi-Leg Hybrid Consignments (Master AWB)
            $assignments = ShipmentAssignment::where('master_awb', $trackingNumberUpper)
                ->orWhereRaw("REPLACE(REPLACE(master_awb, '-', ''), ' ', '') = ?", [$cleanNumber])
                ->orderBy('sequence')
                ->with(['riderProfile', 'partner'])
                ->get();

            if ($assignments->isNotEmpty()) {
                $primaryAssignment = $assignments->first();
                $currentLeg = $assignments->firstWhere('status', '!=', 'completed') ?? $assignments->last();
                return view('tracking.master_awb', compact('assignments', 'primaryAssignment', 'currentLeg', 'trackingNumberUpper'));
            }

            // Not found
            return view('tracking.not-found', ['trackingNumber' => $rawNumber]);
        }

        // Auto-seed initial structured booking milestone if tracking history is empty
        if (empty($shipment->tracking_history)) {
            $this->automatedTracking->recordBookingPlaced($shipment);
            $shipment->refresh();
        }

        $routeCoordinates = $this->resolveFlightRouteCoordinates($shipment);

        return view('tracking.public', compact('shipment', 'routeCoordinates'));
    }

    /**
     * Subscribe customer/consignee for automated tracking milestone alerts (Email/SMS)
     */
    public function subscribe(Request $request)
    {
        $request->validate([
            'tracking_number' => 'required|string',
            'email' => 'nullable|email|required_without:phone',
            'phone' => 'nullable|string|required_without:email|max:25',
        ]);

        $sub = $this->automatedTracking->subscribe(
            $request->tracking_number,
            $request->email,
            $request->phone
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'You have successfully subscribed to automated tracking notifications.',
                'data' => $sub,
            ]);
        }

        return redirect()->back()->with('success', 'You have successfully subscribed to real-time milestone alerts for this consignment.');
    }

    /**
     * Trigger on-demand sync with global last-mile delivery carrier
     */
    public function syncCarrier(Request $request, $shipmentId)
    {
        $shipment = Shipment::findOrFail($shipmentId);
        $this->authorizeTrackingView($request->user(), $shipment);

        $res = $this->carrierSyncService->syncShipment($shipment, $request->user());

        if ($request->wantsJson()) {
            return response()->json($res);
        }

        return redirect()->back()->with($res['success'] ? 'success' : 'error', $res['message']);
    }

    /**
     * Resolve coordinate markers for interactive flight path and hub telemetry map
     */
    private function resolveFlightRouteCoordinates(Shipment $shipment): array
    {
        // Origin: Kathmandu Tribhuvan International Gateway (KTM)
        $origin = [
            'name' => 'Kathmandu Gateway (KTM)',
            'city' => $shipment->sender_city ?: 'Kathmandu',
            'country' => 'Nepal',
            'iata' => 'KTM',
            'lat' => 27.7172,
            'lng' => 85.3240,
        ];

        // Known Global Hub Gateways
        $hubDictionary = [
            'DXB' => ['lat' => 25.2532, 'lng' => 55.3657, 'city' => 'Dubai', 'country' => 'UAE'],
            'DOH' => ['lat' => 25.2731, 'lng' => 51.6081, 'city' => 'Doha', 'country' => 'Qatar'],
            'LHR' => ['lat' => 51.4700, 'lng' => -0.4543, 'city' => 'London', 'country' => 'United Kingdom'],
            'FRA' => ['lat' => 50.0379, 'lng' => 8.5622, 'city' => 'Frankfurt', 'country' => 'Germany'],
            'JFK' => ['lat' => 40.6413, 'lng' => -73.7781, 'city' => 'New York', 'country' => 'USA'],
            'ORD' => ['lat' => 41.9742, 'lng' => -87.9073, 'city' => 'Chicago', 'country' => 'USA'],
            'SYD' => ['lat' => -33.9399, 'lng' => 151.1753, 'city' => 'Sydney', 'country' => 'Australia'],
            'DEL' => ['lat' => 28.5562, 'lng' => 77.1000, 'city' => 'New Delhi', 'country' => 'India'],
            'SIN' => ['lat' => 1.3644, 'lng' => 103.9915, 'city' => 'Singapore', 'country' => 'Singapore'],
            'NRT' => ['lat' => 35.7720, 'lng' => 140.3929, 'city' => 'Tokyo', 'country' => 'Japan'],
        ];

        // Resolve Hub
        $hubCode = strtoupper($shipment->hub?->hub_code ?? 'DXB');
        $hubLat = (float) ($shipment->hub?->latitude ?? ($hubDictionary[$hubCode]['lat'] ?? 25.2532));
        $hubLng = (float) ($shipment->hub?->longitude ?? ($hubDictionary[$hubCode]['lng'] ?? 55.3657));

        $hub = [
            'name' => $shipment->hub?->hub_name ?? "{$hubCode} Global Hub",
            'city' => $shipment->hub?->city ?? ($hubDictionary[$hubCode]['city'] ?? 'Transit Hub'),
            'country' => $shipment->hub?->country ?? ($hubDictionary[$hubCode]['country'] ?? 'Global Gateway'),
            'iata' => $hubCode,
            'lat' => $hubLat,
            'lng' => $hubLng,
        ];

        // Resolve Destination Coordinates
        $destCountry = strtoupper(trim($shipment->receiver_country ?? ''));
        $countryCoordinates = [
            'UNITED STATES' => ['lat' => 38.8951, 'lng' => -77.0364],
            'USA' => ['lat' => 40.7128, 'lng' => -74.0060],
            'US' => ['lat' => 40.7128, 'lng' => -74.0060],
            'UNITED KINGDOM' => ['lat' => 51.5074, 'lng' => -0.1278],
            'UK' => ['lat' => 51.5074, 'lng' => -0.1278],
            'GB' => ['lat' => 51.5074, 'lng' => -0.1278],
            'AUSTRALIA' => ['lat' => -33.8688, 'lng' => 151.2093],
            'AU' => ['lat' => -33.8688, 'lng' => 151.2093],
            'CANADA' => ['lat' => 43.6532, 'lng' => -79.3832],
            'CA' => ['lat' => 43.6532, 'lng' => -79.3832],
            'GERMANY' => ['lat' => 52.5200, 'lng' => 13.4050],
            'FRANCE' => ['lat' => 48.8566, 'lng' => 2.3522],
            'JAPAN' => ['lat' => 35.6762, 'lng' => 139.6503],
            'UNITED ARAB EMIRATES' => ['lat' => 25.2048, 'lng' => 55.2708],
            'UAE' => ['lat' => 25.2048, 'lng' => 55.2708],
            'INDIA' => ['lat' => 28.6139, 'lng' => 77.2090],
        ];

        $destLat = (float) ($shipment->receiver_lat ?? ($countryCoordinates[$destCountry]['lat'] ?? 40.7128));
        $destLng = (float) ($shipment->receiver_lng ?? ($countryCoordinates[$destCountry]['lng'] ?? -74.0060));

        $destination = [
            'name' => $shipment->receiver_city ?: 'Destination City',
            'city' => $shipment->receiver_city ?: 'Destination',
            'country' => $shipment->receiver_country ?: 'Destination Port',
            'lat' => $destLat,
            'lng' => $destLng,
        ];

        return [
            'origin' => $origin,
            'hub' => $hub,
            'destination' => $destination,
        ];
    }

    /**
     * Get live location of a shipment
     */
    public function getLiveLocation($shipmentId)
    {
        $shipment = Shipment::findOrFail($shipmentId);
        $this->authorizeTrackingView(request()->user(), $shipment);
        
        $location = TrackingLocation::where('shipment_id', $shipmentId)
            ->orderBy('recorded_at', 'desc')
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'status' => $shipment->status,
                'status_label' => $this->getStatusLabel($shipment->status),
                'location' => $location ? $location->location_name : ($shipment->current_location ?? null),
                'latitude' => $location ? $location->latitude : ($shipment->current_latitude ?? null),
                'longitude' => $location ? $location->longitude : ($shipment->current_longitude ?? null),
                'last_updated' => $location ? $location->recorded_at->toDateTimeString() : $shipment->updated_at->toDateTimeString(),
            ]
        ]);
    }

    /**
     * Return the latest real rider GPS point for an e-commerce order.
     */
    public function getOrderLiveLocation(Order $order)
    {
        abort_unless($this->canViewOrderLiveLocation(request()->user(), $order), 403,
            'You are not authorized to view this rider location.');

        $location = $order->trackingLocations()->latest('timestamp')->first();
        $lastUpdated = $location?->timestamp ?? $order->rider?->last_location_update;
        $latitude = $location?->latitude ?? $order->rider?->current_latitude;
        $longitude = $location?->longitude ?? $order->rider?->current_longitude;
        $staleAfter = config('tracking.live.stale_after_seconds', 120);

        return response()->json([
            'success' => true,
            'data' => [
                'status' => $order->status,
                'status_label' => $order->status_label,
                'latitude' => $latitude !== null ? (float) $latitude : null,
                'longitude' => $longitude !== null ? (float) $longitude : null,
                'accuracy' => $location?->accuracy !== null ? (float) $location->accuracy : null,
                'speed' => $location?->speed !== null ? (float) $location->speed : null,
                'bearing' => $location?->bearing !== null ? (float) $location->bearing : null,
                'last_updated' => $lastUpdated?->toIso8601String(),
                'is_stale' => !$lastUpdated || $lastUpdated->diffInSeconds(now()) > $staleAfter,
                'delivery' => [
                    'latitude' => $order->delivery_latitude !== null ? (float) $order->delivery_latitude : null,
                    'longitude' => $order->delivery_longitude !== null ? (float) $order->delivery_longitude : null,
                ],
            ],
        ]);
    }

    /**
     * Update shipment location (Rider/Staff)
     */
    public function updateLocation(Request $request, $shipmentId)
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'location_name' => 'nullable|string|max:255',
            'status' => 'nullable|string|in:pending,confirmed,processing,picked_up,in_transit,customs_clearance,out_for_delivery,delivered,failed_delivery,returned,cancelled',
        ]);

        $shipment = Shipment::findOrFail($shipmentId);
        $this->authorizeLocationUpdate($request->user(), $shipment);
        
        // Create tracking location
        $location = TrackingLocation::create([
            'shipment_id' => $shipment->id,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'location_name' => $request->location_name ?? 'En route',
            'status' => $request->status ?? $shipment->status,
            'recorded_at' => now()
        ]);
        
        // Update shipment current location
        $shipment->update([
            'current_latitude' => $request->latitude,
            'current_longitude' => $request->longitude,
            'current_location' => $request->location_name,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Location updated successfully',
            'data' => $location
        ]);
    }

    /**
    /**
     * Update shipment status (Manual Entry with Date, Time, Location & Telemetry)
     */
    public function updateStatus(Request $request, $shipmentId)
    {
        $request->validate([
            'status' => 'nullable|string',
            'event_code' => 'nullable|string',
            'description' => 'nullable|string|max:1000',
            'location' => 'nullable|string|max:255',
            'event_date' => 'nullable|date',
            'event_time' => 'nullable|string',
            'custom_timestamp' => 'nullable|string',
            'mawb_number' => 'nullable|string|max:50',
            'last_mile_carrier_name' => 'nullable|string|max:100',
            'last_mile_tracking_number' => 'nullable|string|max:100',
            'notify_client' => 'nullable|boolean',
        ]);

        $shipment = Shipment::findOrFail($shipmentId);
        $user = auth()->user();

        // Determine the operational event code
        $eventCode = $request->event_code;
        if (empty($eventCode) && !empty($request->status)) {
            $eventCode = $this->scanService->eventCodeForStatus($request->status);
        }

        if (empty($eventCode)) {
            $eventCode = 'in_transit';
        }

        // Check permissions
        $statusToCheck = $request->status ?? 'in_transit';
        $this->authorizeStatusUpdate($user, $shipment, $statusToCheck);

        // Build exact timestamp if custom date/time provided
        $customTimestamp = null;
        if (!empty($request->event_date)) {
            $timePart = !empty($request->event_time) ? $request->event_time : date('H:i:s');
            $customTimestamp = $request->event_date . ' ' . $timePart;
        } elseif (!empty($request->custom_timestamp)) {
            $customTimestamp = $request->custom_timestamp;
        }

        $extraMeta = [
            'mawb_number' => $request->mawb_number,
            'last_mile_carrier_name' => $request->last_mile_carrier_name,
            'last_mile_tracking_number' => $request->last_mile_tracking_number,
            'notify_client' => $request->has('notify_client') ? (bool)$request->notify_client : true,
        ];

        $updated = $this->scanService->record(
            $shipment,
            $eventCode,
            $request->location ?: 'Transit Hub',
            $request->description,
            $user,
            'manual_admin_entry',
            $customTimestamp,
            $extraMeta
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Shipment status and telemetry updated successfully!',
                'shipment' => $updated,
            ]);
        }

        return redirect()->back()
            ->with('success', 'Shipment status and telemetry updated successfully with exact timestamp and location.');
    }


    /**
     * Get tracking history with pagination
     */
    public function getTrackingHistory($shipmentId)
    {
        $shipment = Shipment::findOrFail($shipmentId);
        
        $history = $shipment->tracking_history ?? [];
        
        return response()->json([
            'success' => true,
            'data' => array_reverse($history),
        ]);
    }

    /**
     * Get status label
     */
    private function getStatusLabel($status)
    {
        $labels = [
            'pending' => 'Order Placed',
            'confirmed' => 'Confirmed',
            'processing' => 'Processing',
            'picked_up' => 'Picked Up',
            'in_transit' => 'In Transit',
            'customs_clearance' => 'Customs Clearance',
            'out_for_delivery' => 'Out for Delivery',
            'delivered' => 'Delivered',
            'failed_delivery' => 'Delivery Failed',
            'returned' => 'Returned',
            'cancelled' => 'Cancelled',
        ];
        
        return $labels[$status] ?? ucfirst(str_replace('_', ' ', $status));
    }

    /**
     * Get status description
     */
    private function getStatusDescription($status)
    {
        $descriptions = [
            'pending' => 'Your order has been placed and is being processed',
            'confirmed' => 'Your shipment has been confirmed',
            'processing' => 'Your shipment is being prepared',
            'picked_up' => 'Your shipment has been picked up by the courier',
            'in_transit' => 'Your shipment is on its way to the destination',
            'customs_clearance' => 'Your shipment is going through customs clearance',
            'out_for_delivery' => 'Your shipment is out for delivery',
            'delivered' => 'Your shipment has been successfully delivered',
            'failed_delivery' => 'Delivery attempt was unsuccessful',
            'returned' => 'Your shipment is being returned to sender',
            'cancelled' => 'Your shipment has been cancelled',
        ];
        
        return $descriptions[$status] ?? 'Status updated';
    }

private function authorizeStatusUpdate($user, $shipment, $newStatus)
{
    // Super Admin and Admin can update any status
    if (in_array($user->user_type, ['super_admin', 'admin', 'domestic_admin', 'international_admin'], true)) {
        return true;
    }
    
    // Staff can update specific statuses
    if ($user->user_type === 'staff') {
        $allowedStatuses = ['confirmed', 'processing', 'picked_up', 'in_transit', 'customs_clearance'];
        if (!in_array($newStatus, $allowedStatuses)) {
            abort(403, 'Staff cannot update this status');
        }
        return true;
    }
    
    // Rider can update specific statuses
    if ($user->user_type === 'rider') {
        // Check if rider is assigned to this shipment
        if ($shipment->rider_id !== $user->id) {
            abort(403, 'You are not assigned to this shipment');
        }
        
        $allowedStatuses = ['picked_up', 'in_transit', 'out_for_delivery', 'delivered', 'failed_delivery'];
        if (!in_array($newStatus, $allowedStatuses)) {
            abort(403, 'Rider cannot update this status');
        }
        return true;
    }
    
    // Partner can update specific statuses
    if ($user->user_type === 'partner' || $user->user_type === 'overseas') {
        // Check if partner is associated with this shipment
        $isAssociated = $this->isPartnerAssociated($user, $shipment);
        if (!$isAssociated) {
            abort(403, 'You are not associated with this shipment');
        }
        
        $allowedStatuses = ['picked_up', 'in_transit', 'customs_clearance', 'out_for_delivery'];
        if (!in_array($newStatus, $allowedStatuses)) {
            abort(403, 'Partner cannot update this status');
        }
        return true;
    }
    
    abort(403, 'You are not authorized to update this shipment');
}

private function authorizeTrackingView($user, Shipment $shipment): void
{
    if (in_array($user->user_type, ['super_admin', 'admin', 'staff', 'domestic_admin', 'international_admin'], true)) {
        return;
    }

    $ownerIds = array_map('intval', array_filter([
        $shipment->customer_id,
        $shipment->seller_id,
        $shipment->rider_id,
        $shipment->overseas_partner_id,
    ]));

    if (in_array((int) $user->id, $ownerIds, true)) {
        return;
    }

    if ($this->isPartnerAssociated($user, $shipment)) {
        return;
    }

    abort(403, 'You are not authorized to view this live location.');
}

private function canViewOrderLiveLocation($user, Order $order): bool
{
    if (!$user) {
        return false;
    }

    if (in_array($user->user_type, ['super_admin', 'admin', 'staff', 'domestic_admin'], true)) {
        return true;
    }

    return in_array((int) $user->id, array_filter([
        (int) $order->customer_id,
        (int) $order->client_id,
        (int) $order->seller_id,
        (int) $order->rider_id,
    ]), true);
}

private function authorizeLocationUpdate($user, Shipment $shipment): void
{
    if (in_array($user->user_type, ['super_admin', 'admin', 'staff', 'domestic_admin', 'international_admin'], true)) {
        return;
    }

    if ($user->user_type === 'rider' && (int) $shipment->rider_id === (int) $user->id) {
        return;
    }

    if ($this->isPartnerAssociated($user, $shipment)) {
        return;
    }

    abort(403, 'You are not authorized to update this shipment location.');
}

private function isPartnerAssociated($user, Shipment $shipment): bool
{
    if ($user->user_type === 'overseas') {
        return (int) $shipment->overseas_partner_id === (int) $user->id;
    }

    return $user->user_type === 'partner'
        && $shipment->legs()->where('partner_id', $user->id)->exists();
}

}
