<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use App\Models\User;
use App\Models\MAWB;
use App\Models\LastMileCarrier;
use App\Services\ShipmentScanService;
use App\Services\CarrierTrackingSyncService;
use App\Services\Tracking\MawbFlightTrackingService;
use App\Services\Tracking\CarrierTrackingGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShipmentController extends Controller
{
    public function __construct(
        private readonly ShipmentScanService $scanService,
        private readonly CarrierTrackingSyncService $carrierSyncService,
        private readonly MawbFlightTrackingService $mawbFlightService,
        private readonly CarrierTrackingGateway $carrierGateway
    ) {
    }

    /**
     * Master Consignments List
     */
    public function index(Request $request)
    {
        $query = Shipment::with(['customer', 'mawb', 'lastMileCarrier'])
            ->orderBy('created_at', 'desc');

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->customer_id) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->missing === 'mawb') {
            $query->whereNull('mawb_id')->whereNull('mawb_number');
        } elseif ($request->missing === 'carrier') {
            $query->whereNull('last_mile_tracking_number');
        }

        if ($request->search) {
            $term = trim($request->search);
            $query->where(function($q) use ($term) {
                $q->where('tracking_number', 'like', '%' . $term . '%')
                  ->orWhere('hawb_number', 'like', '%' . $term . '%')
                  ->orWhere('mawb_number', 'like', '%' . $term . '%')
                  ->orWhere('last_mile_tracking_number', 'like', '%' . $term . '%')
                  ->orWhere('receiver_name', 'like', '%' . $term . '%')
                  ->orWhere('sender_name', 'like', '%' . $term . '%');
            });
        }

        $shipments = $query->paginate(20);
        $clients = User::whereIn('user_type', [User::TYPE_CLIENT, User::TYPE_CUSTOMER])
            ->orderBy('name')
            ->get();

        return view('admin.shipments.index', compact('shipments', 'clients'));
    }

    /**
     * Dedicated Tracking Operations & Dispatch Dashboard
     */
    public function trackingDashboard(Request $request)
    {
        $query = Shipment::with(['customer', 'mawb', 'lastMileCarrier'])
            ->orderBy('updated_at', 'desc');

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->filter === 'needs_mawb') {
            $query->whereNull('mawb_id')->whereNull('mawb_number')->whereNotIn('status', ['delivered', 'cancelled']);
        } elseif ($request->filter === 'needs_carrier') {
            $query->whereNull('last_mile_tracking_number')->whereNotIn('status', ['delivered', 'cancelled']);
        } elseif ($request->filter === 'active_flight') {
            $query->whereNotNull('mawb_number')->where('status', 'in_transit');
        } elseif ($request->filter === 'in_last_mile') {
            $query->whereNotNull('last_mile_tracking_number')->whereIn('status', ['in_transit', 'out_for_delivery']);
        }

        if ($request->filled('search')) {
            $term = trim($request->search);
            $query->where(function ($q) use ($term) {
                $q->where('tracking_number', 'like', "%{$term}%")
                    ->orWhere('hawb_number', 'like', "%{$term}%")
                    ->orWhere('mawb_number', 'like', "%{$term}%")
                    ->orWhere('last_mile_tracking_number', 'like', "%{$term}%")
                    ->orWhere('receiver_name', 'like', "%{$term}%");
            });
        }

        $shipments = $query->paginate(25);

        // Operational Telemetry Metrics
        $totalActive = Shipment::whereNotIn('status', ['delivered', 'cancelled'])->count();
        $needsMawbCount = Shipment::whereNull('mawb_number')->whereNotIn('status', ['delivered', 'cancelled'])->count();
        $needsCarrierCount = Shipment::whereNull('last_mile_tracking_number')->whereNotIn('status', ['delivered', 'cancelled'])->count();
        $inFlightCount = Shipment::whereNotNull('mawb_number')->where('status', 'in_transit')->count();
        $deliveredCount = Shipment::where('status', 'delivered')->count();

        $clients = User::whereIn('user_type', [User::TYPE_CLIENT, User::TYPE_CUSTOMER])
            ->orderBy('name')
            ->get();

        return view('admin.tracking.index', compact(
            'shipments',
            'clients',
            'totalActive',
            'needsMawbCount',
            'needsCarrierCount',
            'inFlightCount',
            'deliveredCount'
        ));
    }

    /**
     * Show Comprehensive Tracking & Dispatch Data Entry Console
     */
    public function trackingEntry($id)
    {
        $shipment = Shipment::with(['customer', 'mawb', 'lastMileCarrier', 'issues'])
            ->findOrFail($id);

        $clients = User::whereIn('user_type', [User::TYPE_CLIENT, User::TYPE_CUSTOMER])
            ->orderBy('name')
            ->get();

        $availableMawbs = MAWB::orderBy('created_at', 'desc')->take(40)->get();
        $lastMileCarriers = LastMileCarrier::where('is_active', true)->orderBy('name')->get();

        // Detected airline info if MAWB exists
        $mawbAirlineInfo = null;
        if (!empty($shipment->mawb_number)) {
            $mawbAirlineInfo = $this->mawbFlightService->resolveAirlineByMawb($shipment->mawb_number);
        }

        // Direct carrier URL preview if tracking number exists
        $carrierPreviewUrl = null;
        if (!empty($shipment->last_mile_tracking_number)) {
            $carrierPreviewUrl = $this->carrierGateway->getCarrierUrl(
                $shipment->last_mile_carrier_name,
                $shipment->last_mile_tracking_number
            );
        }

        return view('admin.shipments.tracking', compact(
            'shipment',
            'clients',
            'availableMawbs',
            'lastMileCarriers',
            'mawbAirlineInfo',
            'carrierPreviewUrl'
        ));
    }

    /**
     * Save Comprehensive Tracking Data Entry (Client, MAWB, Last Mile Carrier & Milestone)
     */
    public function updateTracking(Request $request, $id)
    {
        $shipment = Shipment::findOrFail($id);

        $request->validate([
            'customer_id' => 'nullable|exists:users,id',
            'sender_name' => 'nullable|string|max:150',
            'sender_phone' => 'nullable|string|max:50',
            'sender_address' => 'nullable|string|max:255',
            'sender_city' => 'nullable|string|max:100',
            'receiver_name' => 'nullable|string|max:150',
            'receiver_phone' => 'nullable|string|max:50',
            'receiver_address' => 'nullable|string|max:255',
            'receiver_city' => 'nullable|string|max:100',
            'receiver_country' => 'nullable|string|max:100',
            'receiver_postal_code' => 'nullable|string|max:30',
            'mawb_selection_mode' => 'required|in:none,existing,new',
            'existing_mawb_id' => 'nullable|exists:mawbs,id',
            'new_mawb_number' => 'nullable|string|max:50',
            'airline_name' => 'nullable|string|max:100',
            'flight_number' => 'nullable|string|max:50',
            'flight_date' => 'nullable|date',
            'origin_airport' => 'nullable|string|max:100',
            'destination_airport' => 'nullable|string|max:100',
            'last_mile_carrier_name' => 'nullable|string|max:100',
            'last_mile_tracking_number' => 'nullable|string|max:100',
            'record_scan' => 'nullable|boolean',
            'milestone_event' => 'nullable|string',
            'event_date' => 'nullable|date',
            'event_time' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'notify_client' => 'nullable|boolean',
            'trigger_carrier_sync' => 'nullable|boolean',
            'trigger_mawb_sync' => 'nullable|boolean',
        ]);

        DB::transaction(function () use ($request, $shipment) {
            // 1. Client Association & Address Updates
            if ($request->filled('customer_id')) {
                $shipment->customer_id = $request->customer_id;
            }

            foreach (['sender_name', 'sender_phone', 'sender_address', 'sender_city', 'receiver_name', 'receiver_phone', 'receiver_address', 'receiver_city', 'receiver_country', 'receiver_postal_code'] as $field) {
                if ($request->filled($field)) {
                    $shipment->{$field} = $request->{$field};
                }
            }

            // 2. Master Air Waybill (MAWB) Allocation
            $mode = $request->mawb_selection_mode;
            if ($mode === 'existing' && $request->filled('existing_mawb_id')) {
                $mawb = MAWB::find($request->existing_mawb_id);
                if ($mawb) {
                    $shipment->mawb_id = $mawb->id;
                    $shipment->mawb_number = $mawb->mawb_number;
                }
            } elseif ($mode === 'new' && $request->filled('new_mawb_number')) {
                $cleanMawb = trim($request->new_mawb_number);
                $airlineInfo = $this->mawbFlightService->resolveAirlineByMawb($cleanMawb);

                $mawb = MAWB::firstOrCreate(
                    ['mawb_number' => $cleanMawb],
                    [
                        'airline_name' => $request->airline_name ?: ($airlineInfo['name'] ?? 'International Air Cargo'),
                        'airline_code' => $airlineInfo['code'] ?? 'CARGO',
                        'flight_number' => $request->flight_number ?: 'FLIGHT',
                        'flight_date' => $request->flight_date ?: now(),
                        'origin_airport' => $request->origin_airport ?: 'KTM - Tribhuvan International',
                        'destination_airport' => $request->destination_airport ?: ($airlineInfo['hub'] . ' Cargo Hub'),
                        'status' => 'active',
                    ]
                );

                $shipment->mawb_id = $mawb->id;
                $shipment->mawb_number = $mawb->mawb_number;
            } elseif ($mode === 'none') {
                $shipment->mawb_id = null;
                $shipment->mawb_number = null;
            }

            // 3. Last-Mile Delivery Carrier Association
            if ($request->filled('last_mile_carrier_name') || $request->filled('last_mile_tracking_number')) {
                $carrierName = trim($request->last_mile_carrier_name ?: '');
                $trackingNum = trim($request->last_mile_tracking_number ?: '');

                $shipment->last_mile_carrier_name = $carrierName;
                $shipment->last_mile_tracking_number = $trackingNum;

                // Match with registered LastMileCarrier if possible
                $matchedCarrier = LastMileCarrier::where('name', 'like', "%{$carrierName}%")
                    ->orWhere('code', 'like', "%{$carrierName}%")
                    ->first();
                if ($matchedCarrier) {
                    $shipment->last_mile_carrier_id = $matchedCarrier->id;
                }
            }

            $shipment->save();

            // 4. Milestone Scan Recording (if enabled)
            if ($request->boolean('record_scan') && $request->filled('milestone_event')) {
                $eventCode = $request->milestone_event;
                $customTimestamp = null;
                if ($request->filled('event_date')) {
                    $timePart = $request->filled('event_time') ? $request->event_time : date('H:i:s');
                    $customTimestamp = $request->event_date . ' ' . $timePart;
                }

                $this->scanService->record(
                    $shipment,
                    $eventCode,
                    $request->location ?: 'International Transit Hub',
                    $request->description ?: 'Operations scan recorded by staff',
                    auth()->user(),
                    'manual_admin_entry',
                    $customTimestamp,
                    [
                        'mawb_number' => $shipment->mawb_number,
                        'last_mile_carrier_name' => $shipment->last_mile_carrier_name,
                        'last_mile_tracking_number' => $shipment->last_mile_tracking_number,
                        'notify_client' => $request->boolean('notify_client', true),
                    ]
                );
            }
        });

        // 5. Automatic Synchronizations (if triggered)
        $syncMessages = [];
        if ($request->boolean('trigger_carrier_sync') && !empty($shipment->last_mile_tracking_number)) {
            $syncRes = $this->carrierSyncService->syncShipment($shipment);
            if ($syncRes['success']) {
                $syncMessages[] = "Carrier Telemetry: " . ($syncRes['message'] ?? 'Synced successfully');
            }
        }

        if ($request->boolean('trigger_mawb_sync') && $shipment->mawb) {
            $mawbRes = $this->mawbFlightService->syncMawb($shipment->mawb);
            if ($mawbRes['success']) {
                $syncMessages[] = "Air Cargo Flight Radar: " . ($mawbRes['airline']['name'] ?? 'Airline') . " flight status synchronized";
            }
        }

        $flashMessage = 'Consignment tracking data, MAWB allocation, and carrier associations saved successfully!';
        if (!empty($syncMessages)) {
            $flashMessage .= ' | ' . implode(' | ', $syncMessages);
        }

        return redirect()->route('admin.shipments.tracking', $shipment->id)
            ->with('success', $flashMessage);
    }

    /**
     * Trigger Instant Last-Mile Carrier Sync
     */
    public function syncCarrierNow($id)
    {
        $shipment = Shipment::findOrFail($id);
        if (empty($shipment->last_mile_tracking_number)) {
            return back()->with('error', 'Please enter a last-mile carrier tracking number first.');
        }

        $res = $this->carrierSyncService->syncShipment($shipment);
        if ($res['success']) {
            return back()->with('success', 'Carrier Sync Success: ' . ($res['message'] ?? 'Telemetry updated'));
        }

        return back()->with('error', 'Carrier Sync Notice: ' . ($res['message'] ?? 'Could not sync carrier'));
    }

    /**
     * Trigger Instant MAWB Flight Status Sync
     */
    public function syncMawbNow($id)
    {
        $shipment = Shipment::with('mawb')->findOrFail($id);
        if (!$shipment->mawb) {
            return back()->with('error', 'No Master Air Waybill (MAWB) associated with this shipment.');
        }

        $res = $this->mawbFlightService->syncMawb($shipment->mawb);
        if ($res['success']) {
            return back()->with('success', 'Flight Sync Success: Air Cargo flight status updated and cascaded.');
        }

        return back()->with('error', 'Flight Sync Notice: ' . ($res['message'] ?? 'Flight status check completed.'));
    }

    /**
     * Shipment Details View
     */
    public function show($id)
    {
        $shipment = Shipment::with(['customer', 'mawb', 'lastMileCarrier'])->findOrFail($id);
        return view('admin.shipments.show', compact('shipment'));
    }

    /**
     * Quick Status Update
     */
    public function updateStatus(Request $request, $id)
    {
        $shipment = Shipment::findOrFail($id);
        $shipment->update(['status' => $request->status]);
        return back()->with('success', 'Status updated successfully!');
    }
}