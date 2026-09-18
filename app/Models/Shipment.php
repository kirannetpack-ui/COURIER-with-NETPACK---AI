<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shipment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
    // Basic Information
    'hawb_number',
    'tracking_number',
    'customer_id',
    'seller_id',
    'rider_id',
    'overseas_partner_id',
    'current_agency_id',
    'agency_id',
    'current_hub_id',
    'hub_id',
    'current_transit_point_id',
    
    // Sender Information
    'sender_name',
    'sender_phone',
    'sender_address',
    'sender_city',
    'sender_country',
    'sender_lat',
    'sender_lng',
    
    // Receiver Information
    'receiver_name',
    'receiver_phone',
    'receiver_address',
    'receiver_city',
    'receiver_country',
    'receiver_postal_code',
    'receiver_state',
    'receiver_company',
    'receiver_tax_id',
    'receiver_lat',
    'receiver_lng',
    
    // Package Details
    'service_type',
    'shipment_type',
    'actual_weight',
    'volumetric_weight',
    'chargeable_weight',
    'length',
    'width',
    'height',
    'boxes',
    'description',
    'package_type',
    
    // Financial
    'shipping_cost',
    'handling_fee',
    'insurance_fee',
    'total_amount',
    'discount',
    'payment_method',
    'payment_status',
    
    // Status & Tracking
    'status',
    'tracking_history',
    'tracking_timeline',
    'status_notes',
    'current_location',
    'current_latitude',
    'current_longitude',
    
    // Additional Fields
    'pickup_points',
    'delivery_points',
    'estimated_delivery',
    'delivered_at',
    'order_id',
    'store_name',
    'notes',
    
    // Overseas Fields
    'arrived_overseas_at',
    'departed_overseas_at',
    'customs_cleared_at',
    'customs_status',
    'overseas_tracking',
    
    // Agency Fields
    'arrived_at_agency',
    'departed_from_agency',
    'agency_status_history',
    
    // Documents
    'invoice_file',
    'customs_declaration_file',
    'label_file',
    'proof_of_delivery',
    
    // Additional Features
    'requires_signature',
    'is_insured',
    'insurance_amount',
    'is_cod',
    'cod_amount',

    // International Logistics & MAWB Integration
    'mawb_id',
    'mawb_number',
    'last_mile_carrier_id',
    'last_mile_carrier_name',
    'last_mile_tracking_number',
    'customs_mode',
    'agency_milestone',
];

    protected $casts = [
        'boxes' => 'array',
        'tracking_history' => 'array',
        'tracking_timeline' => 'array',
        'pickup_points' => 'array',
        'delivery_points' => 'array',
        'actual_weight' => 'decimal:2',
        'volumetric_weight' => 'decimal:2',
        'chargeable_weight' => 'decimal:2',
        'length' => 'decimal:2',
        'width' => 'decimal:2',
        'height' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'handling_fee' => 'decimal:2',
        'insurance_fee' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'discount' => 'decimal:2',
        'sender_lat' => 'decimal:8',
        'sender_lng' => 'decimal:8',
        'receiver_lat' => 'decimal:8',
        'receiver_lng' => 'decimal:8',
        'estimated_delivery' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    // Relationships
    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function rider()
    {
        return $this->belongsTo(User::class, 'rider_id');
    }

public function currentAgency()
{
    return $this->belongsTo(Agency::class, 'current_agency_id');
}

public function currentHub()
{
    return $this->belongsTo(OverseasHub::class, 'current_hub_id');
}

public function mawb()
{
    return $this->belongsTo(MAWB::class, 'mawb_id');
}

public function lastMileCarrier()
{
    return $this->belongsTo(LastMileCarrier::class, 'last_mile_carrier_id');
}

public function setHubIdAttribute($value)
{
    $this->attributes['current_hub_id'] = $value;
}

public function getHubIdAttribute()
{
    return $this->attributes['current_hub_id'] ?? null;
}

public function setAgencyIdAttribute($value)
{
    $this->attributes['current_agency_id'] = $value;
}

public function getAgencyIdAttribute()
{
    return $this->attributes['current_agency_id'] ?? null;
}


/**
 * Get the overseas partner for this shipment
 */
public function overseasPartner()
{
    return $this->belongsTo(User::class, 'overseas_partner_id');
}

public function legs()
{
    return $this->hasMany(ShipmentLeg::class)->orderBy('sequence');
}


    // Get status badge class
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending' => 'bg-yellow-100 text-yellow-700',
            'confirmed' => 'bg-blue-100 text-blue-700',
            'processing' => 'bg-indigo-100 text-indigo-700',
            'picked_up' => 'bg-purple-100 text-purple-700',
            'in_transit' => 'bg-cyan-100 text-cyan-700',
            'customs_clearance' => 'bg-pink-100 text-pink-700',
            'out_for_delivery' => 'bg-orange-100 text-orange-700',
            'delivered' => 'bg-green-100 text-green-700',
            'returned' => 'bg-gray-100 text-gray-700',
            'cancelled' => 'bg-red-100 text-red-700',
            'failed' => 'bg-gray-100 text-gray-700',
        ];
        return $badges[$this->status] ?? 'bg-gray-100 text-gray-700';
    }

    // Get service type display name
    public function getServiceDisplayAttribute()
    {
        $services = [
            'flash' => 'FLASH (1-2 Hours)',
            'same_day' => 'SAME DAY (4-6 Hours)',
            'standard' => 'STANDARD (1-2 Days)',
            'himalayan' => 'HIMALAYAN (2-4 Days)',
            'economy' => 'Economy (3-5 Days)',
            'express' => 'Express (1-2 Days)',
        ];
        return $services[$this->service_type] ?? $this->service_type;
    }

    // Get shipment type display name
    public function getShipmentTypeDisplayAttribute()
    {
        $types = [
            'domestic' => 'Domestic',
            'international' => 'International',
            'ecommerce' => 'E-Commerce',
            'grocery' => 'Grocery Box',
            'document' => 'Document',
            'parcel' => 'Parcel',
        ];
        return $types[$this->shipment_type] ?? $this->shipment_type;
    }

    // Get payment status display
    public function getPaymentStatusBadgeAttribute()
    {
        $badges = [
            'pending' => 'bg-yellow-100 text-yellow-700',
            'paid' => 'bg-green-100 text-green-700',
            'failed' => 'bg-red-100 text-red-700',
        ];
        return $badges[$this->payment_status] ?? 'bg-gray-100 text-gray-700';
    }

    // Add tracking timeline entry
    public function addTimeline($status, $note = null, $location = null)
    {
        $timeline = $this->tracking_timeline ?? [];
        $timeline[] = [
            'status' => $status,
            'note' => $note,
            'location' => $location,
            'timestamp' => now()->toDateTimeString()
        ];
        $this->tracking_timeline = $timeline;
        $this->save();
    }

    // Scope for customer shipments
    public function scopeForCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    // Scope by status
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

public function transitPoints()
{
    return $this->belongsToMany(OverseasTransitPoint::class, 'shipment_transit_points')
                ->withPivot(['arrived_at', 'departed_at', 'status', 'notes', 'additional_data', 'sequence'])
                ->withTimestamps();
}

public function currentTransitPoint()
{
    return $this->belongsTo(OverseasTransitPoint::class, 'current_transit_point_id');
}

public function getTransitProgressAttribute()
{
    $total = $this->transitPoints()->count();
    $completed = $this->transitPoints()->wherePivot('status', 'departed')->count();
    return $total > 0 ? round(($completed / $total) * 100) : 0;
}

public function getCurrentTransitStatusAttribute()
{
    $current = $this->transitPoints()
        ->wherePivot('status', '!=', 'departed')
        ->orderBy('sequence')
        ->first();

    return $current ? [
        'name' => $current->name,
        'type' => $current->type,
        'status' => $current->pivot->status,
        'arrived_at' => $current->pivot->arrived_at,
    ] : null;
}

/**
 * Generate tracking number with format: [Service Prefix][Year][Month][Random][Check Digit]
 * Format Examples:
 * - Domestic: D-2024-07-001234
 * - International: INT-2024-07-001234
 * - E-commerce: ECOM-2024-07-001234
 * - Flash: FL-2024-07-001234
 * - Same Day: SD-2024-07-001234
 * - Himalayan: HM-2024-07-001234
 */
public static function generateTrackingNumber($serviceType = null, $shipmentType = null)
{
    $service = $shipmentType === 'international'
        ? 'international'
        : ($serviceType === 'ecommerce' ? 'ecommerce' : 'domestic');

    return app(\App\Services\TrackingNumberService::class)->tracking($service);
}

/**
 * Get tracking prefix based on service type
 */
private static function getTrackingPrefix($serviceType, $shipmentType)
{
    $prefixes = [
        'domestic' => [
            'flash' => 'FL',
            'same_day' => 'SD',
            'standard' => 'ST',
            'himalayan' => 'HM',
            'default' => 'DM',
        ],
        'international' => [
            'express' => 'INTE',
            'standard' => 'INTS',
            'economy' => 'INTE',
            'priority' => 'INTP',
            'default' => 'INT',
        ],
        'ecommerce' => [
            'default' => 'ECOM',
        ],
    ];

    // If service type is provided, get specific prefix
    if ($serviceType && isset($prefixes[$shipmentType][$serviceType])) {
        return $prefixes[$shipmentType][$serviceType];
    }
    
    // Get default prefix for shipment type
    if ($shipmentType && isset($prefixes[$shipmentType]['default'])) {
        return $prefixes[$shipmentType]['default'];
    }
    
    return 'NP'; // Default fallback
}

/**
 * Calculate check digit using Luhn algorithm
 */
private static function calculateCheckDigit($number)
{
    $sum = 0;
    $numDigits = strlen($number);
    $parity = $numDigits % 2;
    
    for ($i = 0; $i < $numDigits; $i++) {
        $digit = intval($number[$i]);
        if ($i % 2 == $parity) {
            $digit *= 2;
            if ($digit > 9) {
                $digit -= 9;
            }
        }
        $sum += $digit;
    }
    
    return (10 - ($sum % 10)) % 10;
}

/**
 * Format tracking number for display
 */
public function getFormattedTrackingNumberAttribute()
{
    return strtoupper((string) $this->tracking_number);
}

/**
 * Generate tracking URL for customer
 */
public function getTrackingUrlAttribute()
{
    return route('tracking.show', $this->tracking_number);
}

/**
 * Get tracking status with icon and color
 */
public function getTrackingStatusAttribute()
{
    $statuses = [
        'pending' => [
            'label' => 'Order Placed',
            'icon' => 'fa-clock',
            'color' => 'gray',
            'description' => 'Your order has been placed and is being processed'
        ],
        'confirmed' => [
            'label' => 'Confirmed',
            'icon' => 'fa-check-circle',
            'color' => 'blue',
            'description' => 'Your shipment has been confirmed'
        ],
        'processing' => [
            'label' => 'Processing',
            'icon' => 'fa-spinner',
            'color' => 'yellow',
            'description' => 'Your shipment is being prepared'
        ],
        'picked_up' => [
            'label' => 'Picked Up',
            'icon' => 'fa-box',
            'color' => 'indigo',
            'description' => 'Your shipment has been picked up by the courier'
        ],
        'in_transit' => [
            'label' => 'In Transit',
            'icon' => 'fa-truck',
            'color' => 'purple',
            'description' => 'Your shipment is on its way to the destination'
        ],
        'customs_clearance' => [
            'label' => 'Customs Clearance',
            'icon' => 'fa-clipboard-check',
            'color' => 'orange',
            'description' => 'Your shipment is going through customs clearance'
        ],
        'out_for_delivery' => [
            'label' => 'Out for Delivery',
            'icon' => 'fa-truck-moving',
            'color' => 'teal',
            'description' => 'Your shipment is out for delivery'
        ],
        'delivered' => [
            'label' => 'Delivered',
            'icon' => 'fa-check-circle',
            'color' => 'green',
            'description' => 'Your shipment has been successfully delivered'
        ],
        'failed_delivery' => [
            'label' => 'Delivery Failed',
            'icon' => 'fa-exclamation-triangle',
            'color' => 'red',
            'description' => 'Delivery attempt was unsuccessful'
        ],
        'returned' => [
            'label' => 'Returned',
            'icon' => 'fa-undo-alt',
            'color' => 'gray',
            'description' => 'Your shipment is being returned to sender'
        ],
        'cancelled' => [
            'label' => 'Cancelled',
            'icon' => 'fa-times-circle',
            'color' => 'red',
            'description' => 'Your shipment has been cancelled'
        ]
    ];

    return $statuses[$this->status] ?? [
        'label' => ucfirst($this->status),
        'icon' => 'fa-question-circle',
        'color' => 'gray',
        'description' => 'Status unknown'
    ];
}

protected static function booted()
{
    static::creating(function ($shipment) {
        // Auto-generate tracking number if not set
        if (empty($shipment->tracking_number)) {
            $shipment->tracking_number = self::generateTrackingNumber();
        }
        
        // Auto-generate HAWB number if not set
        if (empty($shipment->hawb_number)) {
            $shipment->hawb_number = $shipment->shipment_type === 'international'
                ? app(\App\Services\TrackingNumberService::class)->internationalHawb($shipment->receiver_country)
                : null;
        }

        // Sender defaults if missing
        if (empty($shipment->sender_name)) {
            $shipment->sender_name = 'Netpack Customer';
        }
        if (empty($shipment->sender_phone)) {
            $shipment->sender_phone = '+977-9800000000';
        }
        if (empty($shipment->sender_address)) {
            $shipment->sender_address = 'Kathmandu, Nepal';
        }

        // Receiver defaults if missing
        if (empty($shipment->receiver_name)) {
            $shipment->receiver_name = 'Destination Consignee';
        }
        if (empty($shipment->receiver_phone)) {
            $shipment->receiver_phone = '+1-555-0199';
        }
        if (empty($shipment->receiver_address)) {
            $shipment->receiver_address = 'Destination Delivery Address';
        }
        if (empty($shipment->receiver_city)) {
            $shipment->receiver_city = $shipment->receiver_country ?? 'Dubai';
        }

        // Weight and pricing defaults
        if (!isset($shipment->actual_weight)) {
            $shipment->actual_weight = 1.0;
        }
        if (!isset($shipment->chargeable_weight)) {
            $shipment->chargeable_weight = $shipment->actual_weight;
        }
        if (!isset($shipment->shipping_cost)) {
            $shipment->shipping_cost = 0;
        }
        if (!isset($shipment->total_amount)) {
            $shipment->total_amount = $shipment->shipping_cost;
        }
    });
}

/**
 * Check if shipment is already manifested
 */
public function isManifested()
{
    return ManifestShipment::where('shipment_id', $this->id)->exists();
}

/**
 * Get the manifest shipment record
 */
public function manifestShipment()
{
    return $this->hasOne(ManifestShipment::class);
}

/**
 * Scope for non-manifested shipments
 */
public function scopeNotManifested($query)
{
    return $query->whereDoesntHave('manifestShipment');
}

public function hub()
{
    return $this->belongsTo(OverseasHub::class, 'current_hub_id');
}

public function agency()
{
    return $this->belongsTo(Agency::class, 'current_agency_id');
}

/**
 * Get the associated doorstep pickup request
 */
public function pickupRequest()
{
    return $this->hasOne(PickupRequest::class, 'shipment_id');
}

/**
 * Get all associated doorstep pickup requests
 */
public function pickupRequests()
{
    return $this->hasMany(PickupRequest::class, 'shipment_id');
}

/**
 * Scope for manifested shipments
 */
public function scopeManifested($query)
{
    return $query->whereHas('manifestShipment');
}

}
