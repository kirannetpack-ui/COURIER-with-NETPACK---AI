<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShipmentIssue extends Model
{
    use HasFactory;

    protected $fillable = [
        'issue_number',
        'shipment_id',
        'customer_id',
        'tracking_number',
        'issue_type',
        'title',
        'situation_description',
        'contact_name',
        'contact_email',
        'contact_phone',
        'claimed_amount',
        'claimed_currency',
        'attachment_file',
        'status',
        'resolution_notes',
        'resolved_by',
        'resolved_at',
    ];

    protected $casts = [
        'claimed_amount' => 'decimal:2',
        'resolved_at' => 'datetime',
    ];

    public static function generateIssueNumber(): string
    {
        return 'ISS-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
    }

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function resolver()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
