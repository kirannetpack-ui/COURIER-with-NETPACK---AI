<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HsCode extends Model
{
    use HasFactory;

    protected $table = 'hs_codes';

    protected $fillable = [
        'code',
        'wco_code',
        'nepal_tariff_code',
        'commodity_name',
        'description',
        'category',
        'standard_uom',
        'export_duty_rate',
        'customs_notes',
        'is_popular',
        'search_keywords',
    ];

    protected $casts = [
        'is_popular' => 'boolean',
        'export_duty_rate' => 'decimal:2',
    ];

    /**
     * Scope popular commodities for 1-click recommendations.
     */
    public function scopePopular($query)
    {
        return $query->where('is_popular', true);
    }

    /**
     * Scope by category.
     */
    public function scopeByCategory($query, ?string $category)
    {
        if (!empty($category) && $category !== 'all') {
            return $query->where('category', $category);
        }
        return $query;
    }

    /**
     * Scope search by keyword, commodity name, or HS code.
     */
    public function scopeSearch($query, ?string $term)
    {
        if (empty($term)) {
            return $query;
        }

        $cleanTerm = trim($term);
        $codeTerm = str_replace(['.', ' ', '-'], '', $cleanTerm);

        return $query->where(function ($q) use ($cleanTerm, $codeTerm) {
            $q->where('commodity_name', 'like', "%{$cleanTerm}%")
              ->orWhere('code', 'like', "%{$cleanTerm}%")
              ->orWhere('wco_code', 'like', "%{$cleanTerm}%")
              ->orWhere('nepal_tariff_code', 'like', "%{$cleanTerm}%")
              ->orWhere('search_keywords', 'like', "%{$cleanTerm}%")
              ->orWhere('description', 'like', "%{$cleanTerm}%");

            if (!empty($codeTerm) && strlen($codeTerm) >= 2) {
                $q->orWhereRaw("REPLACE(REPLACE(code, '.', ''), ' ', '') LIKE ?", ["%{$codeTerm}%"])
                  ->orWhereRaw("REPLACE(REPLACE(wco_code, '.', ''), ' ', '') LIKE ?", ["%{$codeTerm}%"]);
            }
        });
    }

    /**
     * Get display label for UI auto-suggest.
     */
    public function getDisplayLabelAttribute(): string
    {
        return "{$this->commodity_name} (HS: {$this->code})";
    }
}
