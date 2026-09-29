<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Delivery extends Model
{
    protected $fillable = [
        'order_id',
        'rider_id',
        'shipment_id',
        'pickup_request_id',
        'delivery_type',
        'pickup_address',
        'delivery_address',
        'customer_name',
        'customer_phone',
        'special_instructions',
        'status',
        'assigned_at',
        'accepted_at',
        'picked_up_at',
        'in_transit_at',
        'arrived_at',
        'delivered_at',
        'failed_at',
        'pickup_lat',
        'pickup_lng',
        'delivery_lat',
        'delivery_lng',
        'current_lat',
        'current_lng',
        'delivery_fee',
        'rider_earnings',
        'cod_amount',
        'is_cod',
        'proof_of_delivery',
        'delivery_signature',
        'delivery_notes',
        'failure_reason',
        'delivery_number',
        'tracking_number',
        'recipient_name',
        'recipient_phone',
        'recipient_email',
        'address',
        'latitude',
        'longitude',
        'address_type',
        'landmark',
        'instructions',
        'metadata',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'accepted_at' => 'datetime',
        'picked_up_at' => 'datetime',
        'in_transit_at' => 'datetime',
        'arrived_at' => 'datetime',
        'delivered_at' => 'datetime',
        'failed_at' => 'datetime',
        'pickup_lat' => 'decimal:8',
        'pickup_lng' => 'decimal:8',
        'delivery_lat' => 'decimal:8',
        'delivery_lng' => 'decimal:8',
        'current_lat' => 'decimal:8',
        'current_lng' => 'decimal:8',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'delivery_fee' => 'decimal:2',
        'rider_earnings' => 'decimal:2',
        'cod_amount' => 'decimal:2',
        'is_cod' => 'boolean',
        'metadata' => 'array',
    ];

    // =============================================
    // RELATIONSHIPS
    // =============================================
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function rider()
    {
        return $this->belongsTo(User::class, 'rider_id');
    }

    // =============================================
    // SCOPES
    // =============================================
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeAssigned($query)
    {
        return $query->where('status', 'assigned');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['assigned', 'picked_up', 'in_transit', 'out_for_delivery']);
    }

    public function scopeDelivered($query)
    {
        return $query->where('status', 'delivered');
    }

    // =============================================
    // ACCESSORS & MUTATORS
    // =============================================
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending' => 'bg-yellow-100 text-yellow-800',
            'assigned' => 'bg-blue-100 text-blue-800',
            'picked_up' => 'bg-purple-100 text-purple-800',
            'in_transit' => 'bg-indigo-100 text-indigo-800',
            'out_for_delivery' => 'bg-orange-100 text-orange-800',
            'delivered' => 'bg-green-100 text-green-800',
            'cancelled' => 'bg-red-100 text-red-800',
            'failed' => 'bg-gray-100 text-gray-800',
        ];
        return $badges[$this->status] ?? 'bg-gray-100 text-gray-800';
    }

    public function getStatusLabelAttribute()
    {
        $labels = [
            'pending' => 'Pending',
            'assigned' => 'Assigned',
            'picked_up' => 'Picked Up',
            'in_transit' => 'In Transit',
            'out_for_delivery' => 'Out for Delivery',
            'delivered' => 'Delivered',
            'cancelled' => 'Cancelled',
            'failed' => 'Failed',
        ];
        return $labels[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute()
    {
        $colors = [
            'pending' => 'yellow',
            'assigned' => 'blue',
            'picked_up' => 'purple',
            'in_transit' => 'indigo',
            'out_for_delivery' => 'orange',
            'delivered' => 'green',
            'cancelled' => 'red',
            'failed' => 'gray',
        ];
        return $colors[$this->status] ?? 'gray';
    }

    // =============================================
    // HELPER METHODS
    // =============================================
    public static function generateTrackingNumber()
    {
        return app(\App\Services\TrackingNumberService::class)->ecommerce();
    }

    public function isActive()
    {
        return in_array($this->status, ['assigned', 'picked_up', 'in_transit', 'out_for_delivery']);
    }

    public function isCompleted()
    {
        return in_array($this->status, ['delivered', 'cancelled', 'failed']);
    }

    public function getProgressAttribute()
    {
        $progress = [
            'pending' => 0,
            'assigned' => 20,
            'picked_up' => 40,
            'in_transit' => 60,
            'out_for_delivery' => 80,
            'delivered' => 100,
            'cancelled' => 0,
            'failed' => 0,
        ];
        return $progress[$this->status] ?? 0;
    }
}
