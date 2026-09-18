<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    // =============================================
    // USER TYPE CONSTANTS
    // =============================================
    const TYPE_SUPER_ADMIN = 'super_admin';
    // const TYPE_ADMIN = 'admin'; // REMOVED - Use super_admin instead
    const TYPE_STAFF = 'staff';
    const TYPE_DOMESTIC_ADMIN = 'domestic_admin';
    const TYPE_INTERNATIONAL_ADMIN = 'international_admin';
    const TYPE_SELLER = 'seller';
    const TYPE_RIDER = 'rider';
    const TYPE_PARTNER = 'partner';
    const TYPE_OVERSEAS = 'overseas';
    const TYPE_CUSTOMER = 'customer';
    const TYPE_CLIENT = 'client';

    const USER_TYPES = [
        self::TYPE_SUPER_ADMIN => 'Super Administrator',
        'admin' => 'Administrator',
        self::TYPE_STAFF => 'Staff',
        self::TYPE_DOMESTIC_ADMIN => 'Domestic & E-commerce Admin',
        self::TYPE_INTERNATIONAL_ADMIN => 'International Service Admin',
        self::TYPE_SELLER => 'Seller',
        self::TYPE_RIDER => 'Rider',
        self::TYPE_PARTNER => 'Domestic Partner',
        self::TYPE_OVERSEAS => 'Overseas Partner',
        self::TYPE_CUSTOMER => 'Client',
        self::TYPE_CLIENT => 'Client',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'password_changed',
        'user_type',
        'verification_status',
        'phone',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'company_name',
        'business_name',
        'business_address',
        'contact_person',
        'is_online',
        'is_available',
        'vehicle_type',
        'vehicle_number',
        'current_latitude',
        'current_longitude',
        'last_location_update',
        'total_deliveries',
        'total_earnings',
        'rating',
        'rider_deposit_balance',
        'rider_deposit_limit',
        'rider_commission_rate',
        'rider_delivery_fee',
        'rider_margin_rate',
        'bank_name',
        'account_holder_name',
        'account_number',
        'account_type',
        'ifsc_code',
        'email_notifications',
        'sms_notifications',
        'order_updates',
        'registration_completed',
        'approved_at',
        'last_login_at',
        'metadata',
        'gender',
        'dob',
        'nationality',
        'district',
        'province',
        'postal_code',
        'emergency_contact',
        'approved_by',
        'rejection_reason',
        'role',
        'is_active',
        'created_by',
        'service_scope',
        'permanent_address',
        'temporary_address',
        'operating_provinces',
        'operating_districts',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'operating_provinces' => 'array',
        'operating_districts' => 'array',
        'email_verified_at' => 'datetime',
        'approved_at' => 'datetime',
        'last_login_at' => 'datetime',
        'last_location_update' => 'datetime',
        'is_online' => 'boolean',
        'is_available' => 'boolean',
        'registration_completed' => 'boolean',
        'password_changed' => 'boolean',
        'email_notifications' => 'boolean',
        'sms_notifications' => 'boolean',
        'order_updates' => 'boolean',
        'rider_deposit_balance' => 'decimal:2',
        'rider_deposit_limit' => 'decimal:2',
        'rider_commission_rate' => 'decimal:2',
        'rider_delivery_fee' => 'decimal:2',
        'rider_margin_rate' => 'decimal:2',
        'total_earnings' => 'decimal:2',
        'rating' => 'decimal:2',
        'metadata' => 'array',
    ];

    // =============================================
    // RELATIONSHIPS
    // =============================================

    public function shipments()
    {
        return $this->hasMany(Shipment::class, 'seller_id');
    }

    /**
     * Shipments owned by a client/customer. The existing shipment schema uses
     * customer_id for both account types; seller shipments use the relation
     * above.
     */
    public function clientShipments()
    {
        return $this->hasMany(Shipment::class, 'customer_id');
    }

    public function domesticShipments()
    {
        return $this->hasMany(DomesticShipment::class, 'client_id');
    }

    public function shipmentsAsCustomer()
    {
        return $this->clientShipments();
    }

    public function deliveries()
    {
        return $this->hasMany(Delivery::class, 'rider_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'seller_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'user_id');
    }

    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    public function paymentMethods()
    {
        return $this->hasMany(PaymentMethod::class);
    }

    public function riderDeposits()
    {
        return $this->hasMany(RiderDeposit::class, 'rider_id');
    }

    // =============================================
    // HELPER METHODS
    // =============================================

    /**
     * Check if user is a system admin (super admin or staff)
     */
    public function isSystemAdmin()
    {
        return in_array($this->user_type, [self::TYPE_SUPER_ADMIN, 'admin', self::TYPE_STAFF], true);
    }

    /**
     * Check if user is a super admin
     */
    public function isSuperAdmin()
    {
        return $this->user_type === self::TYPE_SUPER_ADMIN;
    }

    /**
     * Check if user is a domestic admin
     */
    public function isDomesticAdmin()
    {
        return $this->user_type === self::TYPE_DOMESTIC_ADMIN;
    }

    /**
     * Check if user is an international admin
     */
    public function isInternationalAdmin()
    {
        return $this->user_type === self::TYPE_INTERNATIONAL_ADMIN;
    }

    /**
     * Check if user is a seller
     */
    public function isSeller()
    {
        return $this->user_type === self::TYPE_SELLER;
    }

    /**
     * Check if user is a rider
     */
    public function isRider()
    {
        return $this->user_type === self::TYPE_RIDER;
    }

    /**
     * Check if user is a partner
     */
    public function isPartner()
    {
        return $this->user_type === self::TYPE_PARTNER;
    }

    /**
     * Check if user is a customer
     */
    public function isCustomer()
    {
        return $this->user_type === self::TYPE_CUSTOMER || $this->user_type === self::TYPE_CLIENT;
    }

    /**
     * Check if user is a client (unified entity with customer)
     */
    public function isClient()
    {
        return $this->isCustomer();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function createdUsers()
    {
        return $this->hasMany(User::class, 'created_by');
    }

    /**
     * Determine effective service scope for staff and administrative routing.
     * Possible values: 'all', 'international', 'domestic', 'ecommerce'
     */
    public function effectiveServiceScope(): string
    {
        if (!empty($this->service_scope)) {
            return strtolower(trim($this->service_scope));
        }

        if ($this->creator) {
            $creatorType = $this->creator->user_type;
            if ($creatorType === self::TYPE_INTERNATIONAL_ADMIN) {
                return 'international';
            }
            if ($creatorType === self::TYPE_DOMESTIC_ADMIN) {
                return 'domestic';
            }
            if ($creatorType === 'ecommerce_admin') {
                return 'ecommerce';
            }
            if ($this->creator->isSuperAdmin() || $creatorType === 'admin') {
                return 'all';
            }
        }

        return 'all';
    }

    public function isSuperAdminStaff(): bool
    {
        return $this->user_type === self::TYPE_STAFF && $this->effectiveServiceScope() === 'all';
    }

    public function isInternationalStaff(): bool
    {
        return $this->user_type === self::TYPE_STAFF && $this->effectiveServiceScope() === 'international';
    }

    public function isDomesticStaff(): bool
    {
        return $this->user_type === self::TYPE_STAFF && $this->effectiveServiceScope() === 'domestic';
    }

    public function isEcommerceStaff(): bool
    {
        return $this->user_type === self::TYPE_STAFF && $this->effectiveServiceScope() === 'ecommerce';
    }

    /**
     * Get the default dashboard route name for this user type.
     */
    public function dashboardRoute(): string
    {
        if ($this->user_type === self::TYPE_STAFF) {
            return match ($this->effectiveServiceScope()) {
                'international' => 'international.dashboard',
                'domestic' => 'domestic.dashboard',
                'ecommerce' => 'domestic.ecommerce.dashboard',
                default => 'admin.dashboard',
            };
        }

        return match ($this->user_type) {
            self::TYPE_SUPER_ADMIN, 'admin' => 'admin.dashboard',
            self::TYPE_DOMESTIC_ADMIN => 'domestic.dashboard',
            self::TYPE_INTERNATIONAL_ADMIN => 'international.dashboard',
            self::TYPE_PARTNER => 'partner.dashboard',
            self::TYPE_OVERSEAS => 'overseas.dashboard',
            self::TYPE_SELLER => 'seller.dashboard',
            self::TYPE_RIDER => 'rider.dashboard',
            self::TYPE_CLIENT, self::TYPE_CUSTOMER => 'client.dashboard',
            default => 'client.dashboard',
        };
    }

    /**
     * Get the default dashboard URL for this user type.
     */
    public function dashboardUrl(): string
    {
        return route($this->dashboardRoute());
    }

    /**
     * Get user type label
     */
    public function getUserTypeLabelAttribute()
    {
        return self::USER_TYPES[$this->user_type] ?? ucfirst($this->user_type);
    }

    /**
     * Get user role badge
     */
    public function getRoleBadgeAttribute()
    {
        $badges = [
            self::TYPE_SUPER_ADMIN => 'bg-purple-100 text-purple-800',
            self::TYPE_STAFF => 'bg-gray-100 text-gray-800',
            self::TYPE_DOMESTIC_ADMIN => 'bg-blue-100 text-blue-800',
            self::TYPE_INTERNATIONAL_ADMIN => 'bg-indigo-100 text-indigo-800',
            self::TYPE_SELLER => 'bg-green-100 text-green-800',
            self::TYPE_RIDER => 'bg-yellow-100 text-yellow-800',
            self::TYPE_PARTNER => 'bg-orange-100 text-orange-800',
            self::TYPE_OVERSEAS => 'bg-pink-100 text-pink-800',
            self::TYPE_CUSTOMER => 'bg-gray-100 text-gray-800',
            self::TYPE_CLIENT => 'bg-gray-100 text-gray-800',
        ];
        return $badges[$this->user_type] ?? 'bg-gray-100 text-gray-800';
    }

    /**
     * Check if rider is online
     */
    public function isOnline()
    {
        return $this->is_online && $this->is_available;
    }

    /**
     * Get rider's deposit balance
     */
    public function getDepositBalanceAttribute()
    {
        return $this->rider_deposit_balance ?? 0;
    }

    /**
     * Check if rider has sufficient deposit for COD
     */
    public function hasSufficientDeposit($amount)
    {
        return ($this->rider_deposit_balance ?? 0) >= $amount;
    }

    /**
     * Deduct from rider deposit
     */
    public function deductDeposit($amount, $description = null)
    {
        $this->rider_deposit_balance -= $amount;
        $this->save();

        // Create deposit record
        RiderDeposit::create([
            'rider_id' => $this->id,
            'amount' => -$amount,
            'balance' => $this->rider_deposit_balance,
            'type' => 'settlement',
            'description' => $description ?? 'Deposit deduction',
            'status' => 'completed',
            'verified_at' => now(),
        ]);

        return $this;
    }

    /**
     * Add to rider deposit
     */
    public function addDeposit($amount, $description = null)
    {
        $this->rider_deposit_balance += $amount;
        $this->save();

        // Create deposit record
        RiderDeposit::create([
            'rider_id' => $this->id,
            'amount' => $amount,
            'balance' => $this->rider_deposit_balance,
            'type' => 'deposit',
            'description' => $description ?? 'Deposit added',
            'status' => 'completed',
            'verified_at' => now(),
        ]);

        return $this;
    }

    /**
     * Get the individual rider profile associated with this user
     */
    public function riderProfile()
    {
        return $this->hasOne(RiderProfile::class, 'user_id');
    }

    /**
     * Ensure this user has an initialized RiderProfile
     */
    public function ensureRiderProfile(): RiderProfile
    {
        $existing = $this->riderProfile()->first();
        if ($existing) {
            $this->setRelation('riderProfile', $existing);
            return $existing;
        }

        $riderCode = 'RDR-' . date('Y') . '-' . str_pad((string) $this->id, 4, '0', STR_PAD_LEFT);
        $profile = RiderProfile::firstOrCreate(
            ['user_id' => $this->id],
            [
                'rider_code' => $riderCode,
                'full_name' => $this->name,
                'mobile' => $this->phone ?? '9800000000',
                'email' => $this->email,
                'dob' => $this->dob,
                'gender' => $this->gender ?? 'male',
                'address' => $this->address ?? 'Kathmandu',
                'province' => $this->province ?? 'Bagmati',
                'district' => $this->district ?? 'Kathmandu',
                'vehicle_type' => in_array($this->vehicle_type ?? '', ['motorcycle', 'scooter', 'bicycle', 'car', 'van']) ? $this->vehicle_type : 'motorcycle',
                'vehicle_number' => $this->vehicle_registration_number ?? 'BA-99-PA-1234',
                'driving_license_number' => $this->license_number ?? '01-06-00001234',
                'verification_status' => $this->verification_status === 'approved' ? 'verified' : 'pending',
                'cod_level' => 'level_1',
                'cod_limit' => 5000.00,
                'current_outstanding_cod' => 0.00,
                'trust_score' => 100,
                'badge_status' => $this->verification_status === 'approved' ? 'verified' : 'new',
                'rating' => 5.00,
                'agreement_accepted' => true,
                'agreement_accepted_at' => now(),
            ]
        );

        $this->setRelation('riderProfile', $profile);
        return $profile;
    }

    /**
     * Get list of partner/user operating provinces.
     */
    public function getOperatingProvinces(): array
    {
        if (is_array($this->operating_provinces) && !empty($this->operating_provinces)) {
            return $this->operating_provinces;
        }
        return !empty($this->province) ? [$this->province] : [];
    }

    /**
     * Get list of partner/user operating districts.
     */
    public function getOperatingDistricts(): array
    {
        if (is_array($this->operating_districts) && !empty($this->operating_districts)) {
            return $this->operating_districts;
        }
        return !empty($this->district) ? [$this->district] : [];
    }

    /**
     * Check if partner/user has established operating coverage.
     */
    public function hasOperatingTerritory(): bool
    {
        return !empty($this->getOperatingDistricts());
    }

    /**
     * Accessor for company_name backed by metadata or business_name
     */
    public function getCompanyNameAttribute(): ?string
    {
        if (array_key_exists('company_name', $this->attributes) && !empty($this->attributes['company_name'])) {
            return $this->attributes['company_name'];
        }
        return $this->metadata['company_name'] ?? $this->attributes['business_name'] ?? null;
    }

    /**
     * Accessor for contact_person backed by metadata
     */
    public function getContactPersonAttribute(): ?string
    {
        if (array_key_exists('contact_person', $this->attributes) && !empty($this->attributes['contact_person'])) {
            return $this->attributes['contact_person'];
        }
        return $this->metadata['contact_person'] ?? null;
    }

    /**
     * Saved addresses for client/user address book.
     */
    public function savedAddresses()
    {
        return $this->hasMany(\App\Models\SavedAddress::class);
    }
}

