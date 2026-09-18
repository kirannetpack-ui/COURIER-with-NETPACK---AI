<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SavedAddress extends Model
{
    use HasFactory;

    protected $table = 'saved_addresses';

    protected $fillable = [
        'user_id',
        'label',
        'type',
        'contact_person_name',
        'contact_person_phone',
        'address',
        'landmark',
        'city',
        'area',
        'is_default',
        'usage_count',
        'last_used_at',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'usage_count' => 'integer',
        'last_used_at' => 'datetime',
    ];

    /**
     * The owning user.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mark this address as used and increment counter.
     */
    public function recordUsage(): self
    {
        $this->usage_count = ($this->usage_count ?? 0) + 1;
        $this->last_used_at = now();
        $this->save();

        return $this;
    }

    /**
     * Helper to get a clean display badge / title.
     */
    public function getDisplayNameAttribute(): string
    {
        if (!empty($this->label)) {
            return $this->label;
        }

        return $this->landmark ?: $this->city;
    }
}
