<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OverseasHub extends Model
{
    use HasFactory;

    protected $fillable = [
        'partner_id',
        'hub_name',
        'name',
        'hub_code',
        'code',
        'location',
        'city',
        'airport_name',
        'country',
        'coverage_countries',
        'main_delivery_countries',
        'transit_countries',
        'service_routes',
        'mode_type',
        'hub_type',
        'address',
        'latitude',
        'longitude',
        'is_active',
        'is_mandatory',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_mandatory' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'sort_order' => 'integer',
        'coverage_countries' => 'array',
        'main_delivery_countries' => 'array',
        'transit_countries' => 'array',
        'service_routes' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($hub) {
            if (empty($hub->hub_name) && !empty($hub->name)) {
                $hub->hub_name = $hub->name;
            }
            if (empty($hub->hub_code) && !empty($hub->code)) {
                $hub->hub_code = strtoupper($hub->code);
            }
            if (empty($hub->location)) {
                $hub->location = $hub->city ?? ($hub->country ? $hub->country . ' Gateway' : 'Transit Hub');
            }
            if (empty($hub->hub_type)) {
                $hub->hub_type = 'main_hub';
            }
            if (empty($hub->address)) {
                $hub->address = $hub->airport_name ?? ($hub->location . ' Air Cargo Terminal');
            }
        });
    }

    public function getNameAttribute()
    {
        return $this->attributes['hub_name'] ?? null;
    }

    public function setNameAttribute($value)
    {
        $this->attributes['hub_name'] = $value;
    }

    public function getCodeAttribute()
    {
        return $this->attributes['hub_code'] ?? null;
    }

    public function setCodeAttribute($value)
    {
        $this->attributes['hub_code'] = strtoupper($value);
    }

    public function getCityAttribute()
    {
        return $this->attributes['location'] ?? null;
    }

    public function setCityAttribute($value)
    {
        if (empty($this->attributes['location'])) {
            $this->attributes['location'] = $value;
        }
    }

    public function getAirportNameAttribute()
    {
        return $this->attributes['address'] ?? null;
    }

    public function setAirportNameAttribute($value)
    {
        if (empty($this->attributes['address'])) {
            $this->attributes['address'] = $value;
        }
    }

    public function getCoverageCountriesAttribute($value)
    {
        if (is_null($value) || $value === '') {
            return [];
        }
        if (is_array($value)) {
            return array_values(array_filter(array_map('trim', $value)));
        }
        $decoded = json_decode($value, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return array_values(array_filter(array_map('trim', $decoded)));
        }
        $parts = preg_split('/[,\r\n]+/', (string) $value);
        return array_values(array_filter(array_map('trim', $parts)));
    }

    public function setCoverageCountriesAttribute($value)
    {
        if (is_null($value) || $value === '') {
            $this->attributes['coverage_countries'] = json_encode([]);
            return;
        }

        if (is_array($value)) {
            $clean = array_values(array_filter(array_map('trim', $value)));
            $this->attributes['coverage_countries'] = json_encode($clean);
            return;
        }

        if (is_string($value)) {
            $trimmed = trim($value);
            $decoded = json_decode($trimmed, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $clean = array_values(array_filter(array_map('trim', $decoded)));
                $this->attributes['coverage_countries'] = json_encode($clean);
                return;
            }

            $parts = preg_split('/[,\r\n]+/', $trimmed);
            $clean = array_values(array_filter(array_map('trim', $parts)));
            $this->attributes['coverage_countries'] = json_encode($clean);
            return;
        }

        $this->attributes['coverage_countries'] = json_encode([]);
    }

    public function getServiceRoutesAttribute($value)
    {
        if (is_null($value) || $value === '') {
            return [];
        }
        if (is_array($value)) {
            return array_values(array_filter(array_map('trim', $value)));
        }
        $decoded = json_decode($value, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return array_values(array_filter(array_map('trim', $decoded)));
        }
        $parts = preg_split('/[,\r\n]+/', (string) $value);
        return array_values(array_filter(array_map('trim', $parts)));
    }

    public function setServiceRoutesAttribute($value)
    {
        if (is_null($value) || $value === '') {
            $this->attributes['service_routes'] = null;
            return;
        }

        if (is_array($value)) {
            $clean = array_values(array_filter(array_map('trim', $value)));
            $this->attributes['service_routes'] = implode(', ', $clean);
            return;
        }

        $this->attributes['service_routes'] = trim((string) $value);
    }

    public function getMainDeliveryCountriesAttribute($value)
    {
        if (is_null($value) || $value === '') {
            return $this->coverage_countries ?? [];
        }
        if (is_array($value)) {
            return array_values(array_filter(array_map('trim', $value)));
        }
        $decoded = json_decode($value, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return array_values(array_filter(array_map('trim', $decoded)));
        }
        $parts = preg_split('/[,\r\n]+/', (string) $value);
        return array_values(array_filter(array_map('trim', $parts)));
    }

    public function setMainDeliveryCountriesAttribute($value)
    {
        $this->attributes['main_delivery_countries'] = $this->serializeCountriesList($value);
    }

    public function getTransitCountriesAttribute($value)
    {
        if (is_null($value) || $value === '') {
            return [];
        }
        if (is_array($value)) {
            return array_values(array_filter(array_map('trim', $value)));
        }
        $decoded = json_decode($value, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return array_values(array_filter(array_map('trim', $decoded)));
        }
        $parts = preg_split('/[,\r\n]+/', (string) $value);
        return array_values(array_filter(array_map('trim', $parts)));
    }

    public function setTransitCountriesAttribute($value)
    {
        $this->attributes['transit_countries'] = $this->serializeCountriesList($value);
    }

    protected function serializeCountriesList($value): ?string
    {
        if (is_null($value) || $value === '') {
            return json_encode([]);
        }
        if (is_array($value)) {
            $clean = array_values(array_filter(array_map('trim', $value)));
            return json_encode($clean);
        }
        if (is_string($value)) {
            $trimmed = trim($value);
            $decoded = json_decode($trimmed, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $clean = array_values(array_filter(array_map('trim', $decoded)));
                return json_encode($clean);
            }
            $parts = preg_split('/[,\r\n]+/', $trimmed);
            $clean = array_values(array_filter(array_map('trim', $parts)));
            return json_encode($clean);
        }
        return json_encode([]);
    }

    public function partner()
    {
        return $this->belongsTo(User::class, 'partner_id');
    }

    public function partners()
    {
        return $this->belongsToMany(Agency::class, 'agency_hub', 'hub_id', 'agency_id')->withTimestamps();
    }

    public function agencies()
    {
        return $this->belongsToMany(Agency::class, 'agency_hub', 'hub_id', 'agency_id')->withTimestamps();
    }

    public function rates()
    {
        return $this->hasMany(InternationalRate::class, 'hub_id');
    }

    public function mawbs()
    {
        return $this->hasMany(MAWB::class, 'hub_id');
    }

    public function lastMileCarriers()
    {
        return $this->hasMany(LastMileCarrier::class, 'hub_id');
    }

    public function shipments()
    {
        return $this->hasMany(Shipment::class, 'current_hub_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByPartner($query, $partnerId)
    {
        return $query->where('partner_id', $partnerId);
    }

    public function getHubTypeLabelAttribute()
    {
        $types = [
            'main_hub' => 'Main Hub',
            'transit_point' => 'Transit Point',
            'sorting_center' => 'Sorting Center',
            'delivery_hub' => 'Delivery Hub',
        ];
        return $types[$this->hub_type] ?? ucfirst($this->hub_type);
    }

    public function getHubTypeColorAttribute()
    {
        $colors = [
            'main_hub' => 'purple',
            'transit_point' => 'blue',
            'sorting_center' => 'orange',
            'delivery_hub' => 'green',
        ];
        return $colors[$this->hub_type] ?? 'gray';
    }

    public function getCoverageSummaryAttribute(): string
    {
        $all = !empty($this->main_delivery_countries) ? $this->main_delivery_countries : ($this->coverage_countries ?? []);
        if (!empty($all) && is_array($all)) {
            return implode(', ', array_slice($all, 0, 4)) . (count($all) > 4 ? ' +' . (count($all) - 4) . ' more' : '');
        }

        return $this->country ?? $this->location ?? 'Global';
    }

    /**
     * Auto figure out pre-defined delivery areas and transit services based on country or hub code.
     */
    public static function autoFigureOutCoverage(?string $country = null, ?string $hubCode = null): array
    {
        $code = strtoupper(trim($hubCode ?? ''));
        $countryName = strtolower(trim($country ?? ''));

        // UAE / Gulf Sector (DXB / DOH)
        if ($code === 'DXB' || $code === 'DOH' || str_contains($countryName, 'emirate') || str_contains($countryName, 'uae') || str_contains($countryName, 'dubai') || str_contains($countryName, 'qatar') || str_contains($countryName, 'saudi')) {
            return [
                'hub_code' => $code ?: 'DXB',
                'country' => 'United Arab Emirates',
                'city' => 'Dubai',
                'airport_name' => 'Dubai International Airport Cargo Terminal (DXB)',
                'main_delivery_countries' => [
                    'United Arab Emirates',
                    'Saudi Arabia',
                    'Qatar',
                    'Oman',
                    'Kuwait',
                    'Bahrain'
                ],
                'transit_countries' => [
                    'Canada',
                    'United States',
                    'United Kingdom',
                    'Germany',
                    'Australia',
                    'Worldwide Cross-Docking'
                ],
                'service_routes' => 'Gulf Cooperation Council (GCC) express door delivery + Cross-docking to North America & Worldwide via Tier-1 linehaul carriers.',
                'mode_type' => 'DDP & Cross Worldwide',
                'suggested_partners' => [
                    ['name' => 'Gulf Express Clearance Co.', 'role' => 'Customs Brokerage & Clearance', 'contact' => 'Tariq Al-Mansoor', 'phone' => '+971-4-2821234', 'email' => 'ops.dubai@netpackcargo.com'],
                    ['name' => 'Middle East Last-Mile Logistics LLC', 'role' => 'Doorstep Delivery Courier', 'contact' => 'Rashid Khan', 'phone' => '+971-4-3334444', 'email' => 'dispatch.dxb@agencyhub.ae'],
                ]
            ];
        }

        // United Kingdom & Western Europe (LHR / LGW / MAN)
        if ($code === 'LHR' || $code === 'LGW' || str_contains($countryName, 'kingdom') || str_contains($countryName, 'britain') || str_contains($countryName, 'london') || str_contains($countryName, 'uk') || str_contains($countryName, 'england')) {
            return [
                'hub_code' => $code ?: 'LHR',
                'country' => 'United Kingdom',
                'city' => 'London',
                'airport_name' => 'London Heathrow World Cargo Centre (LHR)',
                'main_delivery_countries' => [
                    'United Kingdom',
                    'Ireland'
                ],
                'transit_countries' => [
                    'Germany',
                    'France',
                    'Netherlands',
                    'Italy',
                    'Spain',
                    'Poland',
                    'Belgium',
                    'United States',
                    'Canada'
                ],
                'service_routes' => 'UK Nationwide Express DDP Delivery + European Feeder Road Linehaul + Transatlantic Air Cargo injection.',
                'mode_type' => 'UK/EU DDP & USA/CA DDU',
                'suggested_partners' => [
                    ['name' => 'Heathrow European Linehaul & Clearance Ltd', 'role' => 'Customs Broker & Linehaul Operator', 'contact' => 'Oliver Ward', 'phone' => '+44-20-8759-1234', 'email' => 'lhr.ops@netpackcargo.co.uk'],
                    ['name' => 'Royal Express Last-Mile Courier', 'role' => 'UK Domestic Doorstep Dispatch', 'contact' => 'Emily Davies', 'phone' => '+44-20-8759-5678', 'email' => 'dispatch.uk@netpackcargo.co.uk'],
                ]
            ];
        }

        // Central Europe Gateway (FRA / Germany / Poland corridor)
        if ($code === 'FRA' || str_contains($countryName, 'germany') || str_contains($countryName, 'poland') || str_contains($countryName, 'france') || str_contains($countryName, 'netherlands') || str_contains($countryName, 'austria')) {
            return [
                'hub_code' => $code ?: 'FRA',
                'country' => 'Germany',
                'city' => 'Frankfurt',
                'airport_name' => 'Frankfurt CargoCity South (FRA)',
                'main_delivery_countries' => [
                    'Germany',
                    'Poland',
                    'Austria',
                    'Netherlands',
                    'Belgium',
                    'France',
                    'Czech Republic'
                ],
                'transit_countries' => [
                    'Switzerland',
                    'Sweden',
                    'Denmark',
                    'Italy',
                    'Spain',
                    'Hungary',
                    'Romania'
                ],
                'service_routes' => 'Schengen European Union DDP Free-Circulation Gateway & Central European Trucking Linehaul.',
                'mode_type' => 'EU DDP Gateway',
                'suggested_partners' => [
                    ['name' => 'Continental Express Clearance GmbH', 'role' => 'EU Customs & Border Clearance', 'contact' => 'Hans Gruber', 'phone' => '+49-69-690-1234', 'email' => 'fra.ops@netpackcargo.eu'],
                    ['name' => 'Central Europe Linehaul Sp. z o.o.', 'role' => 'Poland & Central Europe Ground Dispatch', 'contact' => 'Piotr Kowalski', 'phone' => '+48-22-123-4567', 'email' => 'poland.ops@netpackcargo.eu'],
                ]
            ];
        }

        // Australia Pacific (SYD / MEL / BNE)
        if ($code === 'SYD' || $code === 'MEL' || str_contains($countryName, 'australia') || str_contains($countryName, 'sydney')) {
            return [
                'hub_code' => $code ?: 'SYD',
                'country' => 'Australia',
                'city' => 'Sydney',
                'airport_name' => 'Sydney Kingsford Smith Airport Cargo Precinct (SYD)',
                'main_delivery_countries' => [
                    'Australia'
                ],
                'transit_countries' => [
                    'New Zealand',
                    'Fiji',
                    'Papua New Guinea',
                    'Pacific Island Nations'
                ],
                'service_routes' => 'Australia Nationwide Interstate Air Express & Pacific Island Transit Feeder.',
                'mode_type' => 'Australia Nationwide',
                'suggested_partners' => [
                    ['name' => 'Sydney Direct Logistics & Customs Pty Ltd', 'role' => 'ABF Customs Brokerage & Airport Intake', 'contact' => 'Liam Smith', 'phone' => '+61-2-9667-1111', 'email' => 'syd.ops@netpackcargo.com.au'],
                    ['name' => 'Aussie Express Doorstep Network', 'role' => 'Nationwide Parcel Delivery', 'contact' => 'Jack Miller', 'phone' => '+61-2-9667-2222', 'email' => 'couriers.au@netpackcargo.com.au'],
                ]
            ];
        }

        // New Zealand (AKL / CHC)
        if ($code === 'AKL' || str_contains($countryName, 'new zealand') || str_contains($countryName, 'auckland')) {
            return [
                'hub_code' => $code ?: 'AKL',
                'country' => 'New Zealand',
                'city' => 'Auckland',
                'airport_name' => 'Auckland Airport Air Cargo Complex (AKL)',
                'main_delivery_countries' => [
                    'New Zealand'
                ],
                'transit_countries' => [
                    'Samoa',
                    'Tonga',
                    'Cook Islands'
                ],
                'service_routes' => 'New Zealand North Island & South Island Express Airfreight & Customs clearance.',
                'mode_type' => 'NZ Nationwide',
                'suggested_partners' => [
                    ['name' => 'Auckland Pacific Express Clearance Ltd', 'role' => 'NZ Customs & Biosecurity Handler', 'contact' => 'Hemi Te Wiata', 'phone' => '+64-9-275-1234', 'email' => 'akl.ops@netpackcargo.co.nz'],
                ]
            ];
        }

        // North America (JFK / ORD / LAX / YYZ)
        if ($code === 'JFK' || $code === 'ORD' || $code === 'LAX' || $code === 'YYZ' || str_contains($countryName, 'united states') || str_contains($countryName, 'usa') || str_contains($countryName, 'america') || str_contains($countryName, 'canada')) {
            return [
                'hub_code' => $code ?: 'JFK',
                'country' => str_contains($countryName, 'canada') ? 'Canada' : 'United States',
                'city' => str_contains($countryName, 'canada') ? 'Toronto' : 'New York',
                'airport_name' => str_contains($countryName, 'canada') ? 'Toronto Pearson International (YYZ)' : 'John F. Kennedy International Airport (JFK)',
                'main_delivery_countries' => [
                    'United States',
                    'Canada'
                ],
                'transit_countries' => [
                    'Mexico',
                    'Puerto Rico',
                    'Bermuda',
                    'Bahamas'
                ],
                'service_routes' => 'North America Coast-to-Coast linehaul, Section 321 customs entry & cross-border Canadian clearance.',
                'mode_type' => 'HYBRID (DDP & DDU)',
                'suggested_partners' => [
                    ['name' => 'North American Gateway Brokerage LLC', 'role' => 'CBP Licensed Customs Broker', 'contact' => 'Michael Chang', 'phone' => '+1-718-555-0100', 'email' => 'jfk.ops@netpackcargo.us'],
                    ['name' => 'Continental Last-Mile Logistics Inc.', 'role' => 'FedEx / UPS Ground Injection Partner', 'contact' => 'Sarah Johnson', 'phone' => '+1-718-555-0101', 'email' => 'dispatch.us@netpackcargo.us'],
                ]
            ];
        }

        // Default General International Hub
        return [
            'hub_code' => $code ?: 'INT',
            'country' => ucwords($country ?? 'Global'),
            'city' => ucwords($country ?? 'Transit City'),
            'airport_name' => (ucwords($country ?? 'International')) . ' Cargo Terminal',
            'main_delivery_countries' => !empty($country) ? [ucwords($country)] : ['Destination Country'],
            'transit_countries' => ['Regional Adjacent Countries'],
            'service_routes' => 'International Air Cargo Intake, Customs Handover & Last-Mile Forwarding.',
            'mode_type' => 'HYBRID',
            'suggested_partners' => [
                ['name' => ucwords($country ?? 'Global') . ' Express Handling Partner', 'role' => 'Handling & Customs Clearance', 'contact' => 'Operations Supervisor', 'phone' => '+000-0000', 'email' => 'ops.' . strtolower($code ?: 'hub') . '@netpack.com'],
            ]
        ];
    }

    /**
     * Dynamically resolve the best hub for a shipment based on admin/staff assignment or country matching.
     */
    public static function resolveHubForShipment(Shipment $shipment): ?self
    {
        // 1. If admin or staff has manually set the current_hub_id, always respect that operational input
        if (!empty($shipment->current_hub_id)) {
            $hub = self::find($shipment->current_hub_id);
            if ($hub && $hub->is_active) {
                return $hub;
            }
        }

        // 2. Auto-figure out based on recipient destination country
        $destCountry = trim($shipment->receiver_country ?? '');
        if (!empty($destCountry)) {
            $matchedHub = self::resolveHubForCountry($destCountry);
            if ($matchedHub) {
                return $matchedHub;
            }
        }

        // 3. Fallback: DXB or first active hub
        return self::where('is_active', true)->where('hub_code', 'DXB')->first()
            ?? self::where('is_active', true)->orderBy('sort_order', 'asc')->first();
    }

    /**
     * Resolve hub by destination country name (matching main delivery first, then transit).
     */
    public static function resolveHubForCountry(string $country): ?self
    {
        $clean = strtolower(trim($country));
        if (empty($clean)) {
            return null;
        }

        $allHubs = self::where('is_active', true)->orderBy('sort_order', 'asc')->get();

        // 1. Check main_delivery_countries (direct primary coverage)
        foreach ($allHubs as $hub) {
            $mainList = array_map('strtolower', (array) $hub->main_delivery_countries);
            foreach ($mainList as $c) {
                if ($c === $clean || str_contains($clean, $c) || str_contains($c, $clean)) {
                    return $hub;
                }
            }
        }

        // 2. Check transit_countries (regional intermediate services)
        foreach ($allHubs as $hub) {
            $transitList = array_map('strtolower', (array) $hub->transit_countries);
            foreach ($transitList as $c) {
                if ($c === $clean || str_contains($clean, $c) || str_contains($c, $clean)) {
                    return $hub;
                }
            }
        }

        // 3. Check legacy coverage_countries
        foreach ($allHubs as $hub) {
            $covList = array_map('strtolower', (array) $hub->coverage_countries);
            foreach ($covList as $c) {
                if ($c === $clean || str_contains($clean, $c) || str_contains($c, $clean)) {
                    return $hub;
                }
            }
        }

        return null;
    }
}