<?php

namespace App\Http\Controllers;

use App\Models\Agency;
use App\Models\LastMileCarrier;
use App\Models\OverseasHub;
use App\Models\Shipment;
use App\Models\DeliveryZone;
use App\Models\PickupRequest;
use App\Models\LogisticsService;
use App\Models\User;
use App\Notifications\DomesticPickupAssignedNotification;
use App\Services\DomesticOperationsNotificationService;
use App\Services\DomesticRateQuoteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ShipmentController extends Controller
{
    /**
     * Display a listing of shipments.
     */
    public function index(Request $request)
{
    $user = Auth::user();
    
    $query = Shipment::query();
    
    // =============================================
    // ROLE-BASED FILTERING
    // =============================================
    
    if ($user->isSuperAdmin() || $user->user_type === 'admin') {
        // Super Admin: See ALL shipments
        // No filter needed
        
    } elseif ($user->isDomesticAdmin()) {
        // Domestic Admin: See DOMESTIC + E-COMMERCE only
        $query->whereIn('shipment_type', ['domestic', 'ecommerce']);
        
    } elseif ($user->isInternationalAdmin()) {
        // International Admin: See INTERNATIONAL only
        $query->where('shipment_type', 'international');
        
    } elseif ($user->isSeller()) {
        // Seller: See only their own shipments
        $query->where('seller_id', $user->id);
        
    } elseif ($user->isRider()) {
        // Rider: See only assigned shipments
        $query->where('rider_id', $user->id);
        
    } elseif ($user->isPartner()) {
        // Partner: canonical assignment is recorded per shipment leg.
        $query->whereHas('legs', fn ($legs) => $legs->where('partner_id', $user->id));
        
    } elseif ($user->isCustomer() || $user->user_type === 'client') {
        // Client / Customer: See strictly their own shipments
        $query->where(function ($q) use ($user) {
            $q->where('customer_id', $user->id)
              ->orWhere(function ($sub) use ($user) {
                  $sub->where('seller_id', $user->id)->whereNull('customer_id');
              });
        });
    }

    // Role-scoped base query for accurate user statistics
    $scopedBase = clone $query;
    
    // Search filter
    if ($request->has('search') && $request->search) {
        $search = $request->search;
        $query->where(function($q) use ($search) {
            $q->where('tracking_number', 'LIKE', "%{$search}%")
              ->orWhere('receiver_name', 'LIKE', "%{$search}%")
              ->orWhere('sender_name', 'LIKE', "%{$search}%")
              ->orWhere('hawb_number', 'LIKE', "%{$search}%");
        });
    }
    
    // Status filter
    if ($request->has('status') && $request->status) {
        if ($request->status === 'ongoing') {
            $query->whereNotIn('status', ['delivered', 'cancelled', 'returned']);
        } else {
            $query->where('status', $request->status);
        }
    }
    
    // Shipment type filter
    if ($request->has('shipment_type') && $request->shipment_type) {
        $query->where('shipment_type', $request->shipment_type);
    }
    
    $shipments = $query->orderBy('created_at', 'desc')->paginate(20);
    
    // Calculate user-scoped statistics
    $stats = [
        'total' => (clone $scopedBase)->count(),
        'pending' => (clone $scopedBase)->where('status', 'pending')->count(),
        'in_transit' => (clone $scopedBase)->whereIn('status', ['picked_up', 'in_transit', 'out_for_delivery'])->count(),
        'delivered' => (clone $scopedBase)->where('status', 'delivered')->count(),
        'cancelled' => (clone $scopedBase)->where('status', 'cancelled')->count(),
    ];
    
    return view('shipments.index', compact('shipments', 'stats'));
}


    /**
     * Show the form for creating a new shipment / Unified Operating Console.
     */
    public function create(Request $request = null)
    {
        $request = $request ?? request();
        $user = Auth::user();
        $hubs = OverseasHub::active()->with('agencies')->orderBy('sort_order')->get();
        $agencies = Agency::where('is_active', true)->get();
        $carriers = LastMileCarrier::active()->orderBy('sort_order')->get();
        $domesticZones = DeliveryZone::active()
            ->where(function ($query) {
                $query->where('approval_status', 'approved')->orWhereNull('approval_status');
            })
            ->orderByRaw("CASE WHEN zone_name LIKE '%Kathmandu%' THEN 0 ELSE 1 END")
            ->orderBy('zone_name')
            ->get();
        $domesticServices = \App\Models\DomesticRate::getServiceTypeOptions();

        // Saved addresses and recent pickup inquiries for rapid prefill
        $savedAddresses = $user ? $user->savedAddresses()->orderByDesc('is_default')->orderByDesc('last_used_at')->get() : collect();
        $recentPickups = $user ? PickupRequest::where('seller_id', $user->id)->latest()->take(10)->get() : collect();

        // Status counts for active pickups & queue tracking
        $pickupStatusCounts = [
            'all' => $user ? PickupRequest::where('seller_id', $user->id)->count() : 0,
            'pending' => $user ? PickupRequest::where('seller_id', $user->id)->where('status', 'pending')->count() : 0,
            'assigned' => $user ? PickupRequest::where('seller_id', $user->id)->where('status', 'assigned')->count() : 0,
            'picked_up' => $user ? PickupRequest::where('seller_id', $user->id)->where('status', 'picked_up')->count() : 0,
            'in_transit' => $user ? PickupRequest::where('seller_id', $user->id)->where('status', 'in_transit')->count() : 0,
            'delivered' => $user ? PickupRequest::where('seller_id', $user->id)->where('status', 'delivered')->count() : 0,
        ];

        // Active dispatches queue for unified console split-pane
        $activePickups = $user ? PickupRequest::where('seller_id', $user->id)->with(['rider', 'partner'])->latest()->take(25)->get() : collect();
        $activeShipments = $user ? Shipment::where('customer_id', $user->id)->orWhere('seller_id', $user->id)->latest()->take(25)->get() : collect();

        // Convert existing pickup request into full consignment pre-fill
        $convertPickup = null;
        $convertPickupId = $request ? $request->input('convert_pickup_id') : request('convert_pickup_id');
        if ($convertPickupId && $user) {
            $convertPickup = PickupRequest::where('seller_id', $user->id)->find($convertPickupId)
                ?? PickupRequest::find($convertPickupId);
        }

        return view('shipments.create', compact(
            'hubs', 'agencies', 'carriers', 'domesticZones', 'domesticServices',
            'savedAddresses', 'recentPickups', 'pickupStatusCounts', 'activePickups', 'activeShipments', 'convertPickup'
        ));
    }

    /**
     * Store a newly created shipment.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'shipment_type' => 'required|in:domestic,international,ecommerce',
            'service_type' => 'required_if:shipment_type,domestic',
            'pickup_name' => 'required|array',
            'pickup_name.*' => 'required|string|max:255',
            'pickup_phone' => 'required|array',
            'pickup_phone.*' => 'required|string|max:20',
            'pickup_address' => 'required|array',
            'pickup_address.*' => 'required|string',
            'weight' => 'required|numeric|min:0.1',
            'description' => 'nullable|string',
            'origin_zone_id' => 'nullable|exists:delivery_zones,id',
            'destination_zone_id' => 'nullable|exists:delivery_zones,id',
        ]);

        // International specific validation
        if ($request->shipment_type === 'international') {
            $validator->addRules([
                'receiver_name' => 'required|string|max:255',
                'receiver_street' => 'required|string|max:255',
                'receiver_city' => 'required|string|max:100',
                'receiver_state' => 'required|string|max:100',
                'receiver_postal_code' => 'required|string|max:20',
                'receiver_country' => 'required|string|max:100',
            ]);
        } else {
            // Domestic/E-commerce validation
            $validator->addRules([
                'delivery_name' => 'required|array',
                'delivery_name.*' => 'required|string|max:255',
                'delivery_phone' => 'required|array',
                'delivery_phone.*' => 'required|string|max:20',
                'delivery_address' => 'required|array',
                'delivery_address.*' => 'required|string',
            ]);
        }

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Get the first pickup for main fields
        $firstPickup = $request->pickup_name[0] ?? '';
        $firstPickupPhone = $request->pickup_phone[0] ?? '';
        $firstPickupAddress = $request->pickup_address[0] ?? '';

        // Generate tracking and HAWB numbers
        $trackingNumber = $this->generateTrackingNumber($request->service_type, $request->shipment_type);
        $hawbNumber = $request->shipment_type === 'international'
            ? $this->generateHAWBNumber($request->receiver_country)
            : null;

        // Auto-resolve origin and destination zones if omitted by client
        $originZoneId = $request->origin_zone_id;
        $destinationZoneId = $request->destination_zone_id;

        if (!$originZoneId) {
            $originZoneId = DeliveryZone::where('is_active', true)
                ->where(function ($q) {
                    $q->where('approval_status', 'approved')->orWhereNull('approval_status');
                })
                ->where(function ($q) {
                    $q->where('zone_name', 'like', '%Kathmandu%')->orWhere('district', 'like', '%Kathmandu%');
                })
                ->value('id')
                ?? DeliveryZone::where('is_active', true)->value('id');
        }

        if (!$destinationZoneId && $originZoneId) {
            $destinationZoneId = DeliveryZone::where('is_active', true)
                ->where(function ($q) {
                    $q->where('approval_status', 'approved')->orWhereNull('approval_status');
                })
                ->where('id', '!=', $originZoneId)
                ->value('id');
        }

        $domesticPlan = null;
        if ($request->shipment_type === 'domestic' && $originZoneId && $destinationZoneId) {
            $domesticPlan = app(DomesticRateQuoteService::class)->quote(
                (int) $originZoneId,
                (int) $destinationZoneId,
                (string) $request->service_type,
                (float) $request->weight,
                null,
                $request->boolean('is_cod')
            );
        } elseif ($request->shipment_type === 'international' && $originZoneId && $destinationZoneId) {
            // International bookings include the domestic first-mile movement to the selected gateway.
            $domesticPlan = app(DomesticRateQuoteService::class)->quote(
                (int) $originZoneId,
                (int) $destinationZoneId,
                'standard',
                (float) $request->weight
            );
        }

        // Customer price is derived from approved partner cost plus the admin margin.
        $rate = $this->calculateRate($request) + (float) ($domesticPlan['customer_price'] ?? 0);

        DB::beginTransaction();
        try {

        $shipment = new Shipment();
        $shipment->hawb_number = $hawbNumber;
        $shipment->tracking_number = $trackingNumber;
        $shipment->customer_id = Auth::id();
        $shipment->service_type = $request->service_type ?? 'standard';
        $shipment->shipment_type = $request->shipment_type;
        
        // Sender (from profile)
        $user = Auth::user();
        $shipment->sender_name = $user->name;
        $shipment->sender_phone = $user->phone ?? 'N/A';
        $shipment->sender_address = $user->permanent_address ?? $firstPickupAddress;
        $shipment->sender_city = $user->city ?? 'Kathmandu';
        $shipment->sender_country = 'Nepal';
        $shipment->sender_lat = $user->address_lat;
        $shipment->sender_lng = $user->address_lng;

        // Receiver based on shipment type
        if ($request->shipment_type === 'international') {
            // International: Single receiver with 5-line format
            $shipment->receiver_name = $request->receiver_name;
            $shipment->receiver_phone = $request->receiver_phone ?? 'N/A';
            $shipment->receiver_address = $request->receiver_street;
            $shipment->receiver_city = $request->receiver_city;
            $shipment->receiver_state = $request->receiver_state;
            $shipment->receiver_postal_code = $request->receiver_postal_code;
            $shipment->receiver_country = $request->receiver_country;
            $shipment->receiver_tax_id = $request->receiver_tax_id;
            $shipment->receiver_company = $request->receiver_company;
            
            // Store full address for display
            $fullAddress = $request->receiver_name . "\n" .
                          $request->receiver_street . "\n" .
                          $request->receiver_city . ", " . $request->receiver_state . "\n" .
                          $request->receiver_postal_code . "\n" .
                          $request->receiver_country;
            $shipment->receiver_address = $fullAddress;
            
            // Multiple delivery points for international (if provided) or single 5-line format
            if (is_array($request->delivery_name) && count($request->delivery_name) > 0 && !empty($request->delivery_name[0])) {
                $deliveryPoints = [];
                for ($i = 0; $i < count($request->delivery_name); $i++) {
                    $deliveryPoints[] = [
                        'name' => $request->delivery_name[$i],
                        'phone' => $request->delivery_phone[$i] ?? 'N/A',
                        'address' => $request->delivery_address[$i] ?? '',
                        'city' => $request->receiver_city ?? '',
                        'country' => $request->receiver_country ?? '',
                        'lat' => $request->delivery_lat[$i] ?? null,
                        'lng' => $request->delivery_lng[$i] ?? null,
                    ];
                }
                $shipment->delivery_points = $deliveryPoints;
            } else {
                $shipment->delivery_points = [
                    [
                        'name' => $request->receiver_name,
                        'phone' => $request->receiver_phone ?? 'N/A',
                        'address' => $fullAddress,
                        'city' => $request->receiver_city,
                        'state' => $request->receiver_state,
                        'postal_code' => $request->receiver_postal_code,
                        'country' => $request->receiver_country,
                    ]
                ];
            }
            
        } else {
            // Domestic/E-commerce: Multiple delivery points
            $firstDelivery = $request->delivery_name[0] ?? '';
            $firstDeliveryPhone = $request->delivery_phone[0] ?? '';
            $firstDeliveryAddress = $request->delivery_address[0] ?? '';
            $firstDeliveryDistrict = is_array($request->delivery_district) ? ($request->delivery_district[0] ?? null) : $request->delivery_district;
            $firstDeliveryProvince = is_array($request->delivery_province) ? ($request->delivery_province[0] ?? null) : $request->delivery_province;

            $shipment->receiver_name = $firstDelivery;
            $shipment->receiver_phone = $firstDeliveryPhone;
            $shipment->receiver_address = $firstDeliveryAddress;
            $shipment->receiver_city = $firstDeliveryDistrict ?: ($request->receiver_city ?? 'Kathmandu');
            $shipment->receiver_state = $firstDeliveryProvince ?: ($request->receiver_state ?? 'Bagmati');
            $shipment->receiver_country = $request->receiver_country ?? 'Nepal';

            // Multiple delivery points
            $deliveryPoints = [];
            for ($i = 0; $i < count($request->delivery_name); $i++) {
                $deliveryPoints[] = [
                    'name' => $request->delivery_name[$i],
                    'phone' => $request->delivery_phone[$i],
                    'address' => $request->delivery_address[$i],
                    'district' => is_array($request->delivery_district) ? ($request->delivery_district[$i] ?? null) : null,
                    'province' => is_array($request->delivery_province) ? ($request->delivery_province[$i] ?? null) : null,
                    'lat' => $request->delivery_lat[$i] ?? null,
                    'lng' => $request->delivery_lng[$i] ?? null,
                ];
            }
            $shipment->delivery_points = $deliveryPoints;
        }

        // Multiple pickup points for ALL shipment types (Domestic, International, E-Commerce)
        $pickupPoints = [];
        if (is_array($request->pickup_name)) {
            for ($i = 0; $i < count($request->pickup_name); $i++) {
                if (!empty($request->pickup_name[$i]) || !empty($request->pickup_address[$i])) {
                    $pickupPoints[] = [
                        'name' => $request->pickup_name[$i] ?? $firstPickup,
                        'phone' => $request->pickup_phone[$i] ?? $firstPickupPhone,
                        'address' => $request->pickup_address[$i] ?? $firstPickupAddress,
                        'lat' => $request->pickup_lat[$i] ?? null,
                        'lng' => $request->pickup_lng[$i] ?? null,
                    ];
                }
            }
        }
        if (empty($pickupPoints) && $firstPickup) {
            $pickupPoints[] = [
                'name' => $firstPickup,
                'phone' => $firstPickupPhone,
                'address' => $firstPickupAddress,
            ];
        }
        $shipment->pickup_points = $pickupPoints;

        // Auto-save newly entered pickup addresses to user's address book
        if ($request->boolean('save_pickup_addresses', true) && $user) {
            foreach ($pickupPoints as $pPoint) {
                if (!empty($pPoint['address'])) {
                    $sAddr = \App\Models\SavedAddress::firstOrNew([
                        'user_id' => $user->id,
                        'address' => $pPoint['address'],
                    ]);
                    $sAddr->contact_person_name = $pPoint['name'] ?? $user->name;
                    $sAddr->contact_person_phone = $pPoint['phone'] ?? ($user->phone ?? '9800000000');
                    if (empty($sAddr->label)) {
                        $sAddr->label = 'Pickup Location #' . ($user->savedAddresses()->count() + 1);
                    }
                    $sAddr->recordUsage();
                }
            }
        }

        // Package details & precision air cargo weight calculation
        $grossWeight = max(0.1, (float) $request->weight);
        $length = $request->filled('length') ? (float)$request->length : null;
        $width = $request->filled('width') ? (float)$request->width : null;
        $height = $request->filled('height') ? (float)$request->height : null;

        if ($request->shipment_type === 'international') {
            $weightInfo = app(\App\Services\InternationalRateService::class)->calculateWeight(
                $grossWeight, $length, $width, $height
            );
            $shipment->actual_weight = $weightInfo['gross_weight'];
            $shipment->volumetric_weight = $weightInfo['volumetric_weight'];
            $shipment->chargeable_weight = $weightInfo['chargeable_weight'];
        } else {
            $shipment->actual_weight = $grossWeight;
            $shipment->chargeable_weight = $grossWeight;
        }

        $shipment->length = $length;
        $shipment->width = $width;
        $shipment->height = $height;
        $shipment->description = $request->description;
        $shipment->package_type = $request->package_type ?? 'parcel';

        // Pricing
        $shipment->shipping_cost = $rate;
        $shipment->handling_fee = 0;
        $shipment->insurance_fee = 0;
        $shipment->total_amount = $rate;
        $shipment->discount = 0;

        // E-commerce specific
        if ($request->shipment_type === 'ecommerce') {
            $shipment->order_id = $request->order_id;
            $shipment->store_name = $request->store_name;
        }

        // Status
        $shipment->status = 'pending';
        $shipment->payment_status = 'pending';

        // Tracking timeline
        $shipment->tracking_timeline = [
            [
                'status' => 'pending',
                'note' => 'Shipment created',
                'timestamp' => now()->toDateTimeString()
            ]
        ];

        // Estimated delivery & routing assignment (defined by admin post-booking)
        if ($request->shipment_type === 'international') {
            if ($request->service_type === 'express') {
                $shipment->estimated_delivery = now()->addDays(4);
            } else {
                $shipment->estimated_delivery = now()->addDays(8);
            }
            $shipment->last_mile_carrier_name = null;
            $shipment->current_hub_id = null;
            $shipment->current_agency_id = null;
            $shipment->customs_mode = 'DDP';
            $shipment->agency_milestone = 'pending_admin_routing';
        } else {
            $shipment->estimated_delivery = now()->addDays(3);
        }

        $shipment->save();

        // Automatically initialize booking milestone in tracking history
        app(\App\Services\AutomatedTrackingService::class)->recordBookingPlaced(
            $shipment,
            $shipment->sender_city ?: 'Kathmandu Gateway',
            Auth::user()
        );

        // Link or create doorstep pickup request if requested
        $createdPickup = null;
        if ($request->filled('convert_pickup_id') && $user) {
            $existingPickup = PickupRequest::where('seller_id', $user->id)->find($request->convert_pickup_id);
            if ($existingPickup) {
                $existingPickup->shipment_id = $shipment->id;
                $existingPickup->status_notes = ($existingPickup->status_notes ? $existingPickup->status_notes . ' | ' : '') . 'Upgraded to Consignment ' . ($shipment->hawb_number ?: $shipment->tracking_number);
                $existingPickup->save();
            }
        } elseif ($request->boolean('schedule_doorstep_pickup') && $user) {
            $pickupTime = $request->filled('scheduled_pickup_time')
                ? \Carbon\Carbon::parse($request->scheduled_pickup_time)
                : now()->addHours(2);

            $primaryPickup = $pickupPoints[0] ?? [
                'name' => $firstPickup,
                'phone' => $firstPickupPhone,
                'address' => $firstPickupAddress,
            ];

            $createdPickup = PickupRequest::create([
                'seller_id' => $user->id,
                'shipment_id' => $shipment->id,
                'contact_person_name' => $primaryPickup['name'] ?? $user->name,
                'contact_person_phone' => $primaryPickup['phone'] ?? ($user->phone ?? '9800000000'),
                'pickup_address' => $primaryPickup['address'] ?? ($user->address ?? 'Kathmandu'),
                'pickup_city' => $shipment->sender_city ?? 'Kathmandu Valley',
                'pickup_latitude' => $primaryPickup['lat'] ?? null,
                'pickup_longitude' => $primaryPickup['lng'] ?? null,
                'delivery_address' => $shipment->receiver_address,
                'delivery_city' => $shipment->receiver_city ?? 'Destination',
                'customer_name' => $shipment->receiver_name,
                'customer_phone' => $shipment->receiver_phone,
                'items_description' => 'Doorstep Collection for Consignment ' . ($shipment->hawb_number ?: $shipment->tracking_number) . ' (' . $shipment->package_type . ')',
                'estimated_weight_kg' => $shipment->chargeable_weight ?: $shipment->actual_weight,
                'service_tier' => $shipment->service_type ?: 'standard',
                'scheduled_pickup_time' => $pickupTime,
                'status' => 'pending',
                'status_notes' => 'Doorstep collection scheduled with Consignment ' . ($shipment->hawb_number ?: $shipment->tracking_number),
            ]);
        }

        if ($domesticPlan) {
            $this->persistDomesticPlan($shipment, $domesticPlan, $request);
        }

        DB::commit();

        $pickupNote = $createdPickup ? " & Doorstep Pickup Scheduled (Ref: {$createdPickup->tracking_number})" : "";
        return redirect()->route('tracking.show', $shipment->tracking_number)
            ->with('success', 'Shipment created successfully! Tracking number: ' . $shipment->tracking_number . $pickupNote);
        } catch (\Throwable $exception) {
            DB::rollBack();
            report($exception);

            return back()->withInput()->withErrors([
                'shipment' => 'The shipment could not be booked safely. No charge was recorded. Please try again or contact operations.',
            ]);
        }
    }

    /**
     * Display the specified shipment.
     */
    public function show(Shipment $shipment)
    {
        return view('tracking.public', compact('shipment'));
    }

    /**
     * Show the form for editing the specified shipment.
     */
    public function edit($id)
    {
        $shipment = Shipment::findOrFail($id);
        
        // Check authorization
        $user = Auth::user();
        if ($shipment->customer_id !== $user->id && !$user->isSuperAdmin() && !$user->isDomesticAdmin()) {
            abort(403, 'Unauthorized action.');
        }
        
        return view('shipments.edit', compact('shipment'));
    }

    /**
     * Update the specified shipment.
     */
    public function update(Request $request, $id)
    {
        $shipment = Shipment::findOrFail($id);
        
        // Check authorization
        $user = Auth::user();
        if ($shipment->customer_id !== $user->id && !$user->isSuperAdmin() && !$user->isDomesticAdmin()) {
            abort(403, 'Unauthorized action.');
        }
        
        $validator = Validator::make($request->all(), [
            'receiver_name' => 'required|string|max:255',
            'receiver_phone' => 'required|string|max:20',
            'receiver_address' => 'required|string',
            'weight' => 'required|numeric|min:0.1',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Only allow updates if status is pending
        if ($shipment->status !== 'pending' && $shipment->status !== 'confirmed') {
            return redirect()->back()
                ->with('error', 'Shipment cannot be edited. Current status: ' . ucfirst($shipment->status));
        }

        $shipment->update([
            'receiver_name' => $request->receiver_name,
            'receiver_phone' => $request->receiver_phone,
            'receiver_address' => $request->receiver_address,
            'receiver_city' => $request->receiver_city ?? $shipment->receiver_city,
            'receiver_country' => $request->receiver_country ?? $shipment->receiver_country,
            'actual_weight' => $request->weight,
            'chargeable_weight' => $request->weight,
            'description' => $request->description,
        ]);

        return redirect()->route('tracking.show', $shipment->tracking_number)
            ->with('success', 'Shipment updated successfully!');
    }

    /**
     * Remove the specified shipment.
     */
    public function destroy($id)
    {
        $shipment = Shipment::findOrFail($id);
        
        // Check authorization
        $user = Auth::user();
        if ($shipment->customer_id !== $user->id && !$user->isSuperAdmin() && !$user->isDomesticAdmin()) {
            abort(403, 'Unauthorized action.');
        }
        
        // Only allow deletion if status is pending
        if ($shipment->status !== 'pending') {
            return redirect()->back()
                ->with('error', 'Cannot delete shipment. Shipment is already in progress.');
        }
        
        $shipment->delete();
        
        return redirect()->route('shipments.index')
            ->with('success', 'Shipment deleted successfully!');
    }

    /**
     * Update shipment status.
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,processing,picked_up,in_transit,customs_clearance,out_for_delivery,delivered,returned,cancelled,failed',
            'note' => 'nullable|string',
            'location' => 'nullable|string',
        ]);

        $shipment = Shipment::findOrFail($id);
        $oldStatus = $shipment->status;
        $newStatus = $request->status;
        
        $shipment->status = $newStatus;
        
        // Add to tracking timeline
        $timeline = $shipment->tracking_timeline ?? [];
        $timeline[] = [
            'status' => $newStatus,
            'note' => $request->note ?? "Status updated from {$oldStatus} to {$newStatus}",
            'location' => $request->location ?? 'System',
            'timestamp' => now()->toDateTimeString(),
            'updated_by' => Auth::user()->name,
        ];
        $shipment->tracking_timeline = $timeline;

        if ($newStatus === 'delivered') {
            $shipment->delivered_at = now();
        }

        $shipment->save();

        return response()->json([
            'success' => true,
            'message' => 'Shipment status updated successfully',
            'shipment' => $shipment
        ]);
    }

    /**
     * Get tracking information via API.
     */
    public function getTracking($trackingNumber)
    {
        $shipment = Shipment::where('tracking_number', $trackingNumber)
            ->with(['customer', 'seller', 'rider'])
            ->firstOrFail();
        
        return response()->json([
            'success' => true,
            'shipment' => $shipment
        ]);
    }

    /**
     * Generate tracking number.
     */
    private function generateTrackingNumber(?string $serviceType = null, ?string $shipmentType = null)
    {
        return Shipment::generateTrackingNumber($serviceType, $shipmentType);
    }

    /**
     * Generate HAWB number.
     */
    private function generateHAWBNumber(?string $destinationCountry)
    {
        return app(\App\Services\TrackingNumberService::class)->internationalHawb($destinationCountry);
    }

    /**
     * Calculate shipping rate.
     */
    private function calculateRate($request)
    {
        $weight = $request->weight ?? 0;
        $type = $request->shipment_type;
        $service = $request->service_type ?? 'standard';

        if ($type === 'domestic') {
            // Domestic pricing comes exclusively from approved partner rates.
            return 0;
        } elseif ($type === 'international') {
            try {
                $quote = app(\App\Services\InternationalRateService::class)->quote(
                    $request->receiver_country ?? 'United States',
                    (float) $weight,
                    $request->length ? (float)$request->length : null,
                    $request->width ? (float)$request->width : null,
                    $request->height ? (float)$request->height : null,
                    'none',
                    $service
                );
                if (!empty($quote['quotes'][0]['itemized']['total_cost'])) {
                    return (float) $quote['quotes'][0]['itemized']['total_cost'];
                }
            } catch (\Throwable $e) {
                // Fallback to basic formula if matrix lookup fails
            }

            switch($service) {
                case 'express':
                    return 800 + ($weight * 150);
                case 'economy':
                default:
                    return 500 + ($weight * 80);
            }
        } else {
            // E-commerce
            return 100 + ($weight * 25);
        }
    }

    private function persistDomesticPlan(Shipment $shipment, array $plan, Request $request): void
    {
        $createdLegs = collect($plan['legs'])->values()->map(function (array $legData, int $index) use ($shipment, $plan, $request) {
            $rate = $legData['rate'];

            return $shipment->legs()->create([
                'sequence' => $index + 1,
                'leg_type' => $rate->rate_type,
                'partner_id' => $rate->partner_id,
                'domestic_rate_id' => $rate->id,
                'origin_zone_id' => $rate->origin_zone_id,
                'destination_zone_id' => $rate->destination_zone_id,
                'origin_name' => $rate->originZone?->zone_name ?? $rate->origin_city,
                'destination_name' => $rate->destinationZone?->zone_name ?? $rate->destination_city,
                'status' => 'assigned',
                'assignment_source' => $legData['assignment_source'],
                'selected_by' => $request->user()->id,
                'partner_cost' => $legData['partner_cost'],
                'markup_amount' => $legData['markup_amount'],
                'customer_price' => $legData['customer_price'],
                'currency' => $plan['currency'],
                'metadata' => ['quote_expires_at' => $plan['expires_at'], 'breakdown' => $legData['breakdown']],
            ]);
        });

        $firstLeg = $createdLegs->first();
        if ($firstLeg) {
            $origin = $plan['origin_zone'];
            $destination = $plan['destination_zone'];
            PickupRequest::create([
                'seller_id' => $shipment->customer_id,
                'shipment_id' => $shipment->id,
                'partner_user_id' => $firstLeg->partner_id,
                'pickup_address' => $request->pickup_address[0],
                'pickup_ward_no' => 'N/A',
                'pickup_municipality' => $origin->zone_name,
                'pickup_district' => $origin->district ?: ($origin->districts[0] ?? $origin->zone_name),
                'pickup_province' => $origin->province ?: 'Not specified',
                'delivery_address' => $shipment->receiver_address,
                'delivery_ward_no' => 'N/A',
                'delivery_municipality' => $destination->zone_name,
                'delivery_district' => $destination->district ?: ($destination->districts[0] ?? $destination->zone_name),
                'delivery_province' => $destination->province ?: 'Not specified',
                'scheduled_pickup_time' => now(),
                'items_description' => $shipment->description ?: 'Parcel shipment',
                'estimated_weight_kg' => $shipment->chargeable_weight,
                'service_tier' => in_array($shipment->service_type, ['flash', 'same_day', 'standard', 'himalayan'], true) ? $shipment->service_type : 'standard',
                'status' => 'assigned',
                'calculated_price' => $plan['customer_price'],
                'tracking_number' => $shipment->tracking_number,
            ]);
        }

        $notifiedPartners = [];
        foreach ($createdLegs as $leg) {
            if ($leg->partner && ! in_array($leg->partner_id, $notifiedPartners, true)) {
                $leg->partner->notify(new DomesticPickupAssignedNotification($shipment, $leg));
                $notifiedPartners[] = $leg->partner_id;
            }
        }

        if ($firstLeg) {
            app(DomesticOperationsNotificationService::class)
                ->notify(new DomesticPickupAssignedNotification($shipment, $firstLeg));
        }
    }
}
