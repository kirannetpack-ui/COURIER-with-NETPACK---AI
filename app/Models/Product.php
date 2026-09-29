<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'description',
        'category',
        'price',
        'price_npr',
        'price_usd',
        'discount_price',
        'sku',
        'stock_quantity',
        'weight_kg',
        'length_cm',
        'width_cm',
        'height_cm',
        'origin_country',
        'origin_city',
        'is_active',
        'is_featured',
        'image_url',
        'image_path',
        'images',
        'tags',
        'dimensions',
        'features',
        'metadata',
    ];

    protected $casts = [
        'images' => 'array',
        'features' => 'array',
        'metadata' => 'array',
        'price' => 'decimal:2',
        'price_npr' => 'decimal:2',
        'price_usd' => 'decimal:2',
        'discount_price' => 'decimal:2',
        'weight_kg' => 'decimal:2',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orders()
    {
        return $this->belongsToMany(Order::class, 'order_items')
            ->withPivot(['quantity', 'price'])
            ->withTimestamps();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInStock($query)
    {
        return $query->where('stock_quantity', '>', 0);
    }
}