<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Wallet;
use App\Models\RiderProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new RuntimeException('Demo accounts may only be seeded in local or testing environments.');
        }

        $accounts = [
            ['name' => 'Super Administrator', 'email' => 'superadmin@netpack.test', 'password' => 'Netpack!Admin#2026', 'user_type' => 'super_admin'],
            ['name' => 'Domestic Administrator', 'email' => 'domestic.admin@netpack.test', 'password' => 'Netpack!Domestic#2026', 'user_type' => 'domestic_admin'],
            ['name' => 'International Administrator', 'email' => 'international.admin@netpack.test', 'password' => 'Netpack!International#2026', 'user_type' => 'international_admin'],
            ['name' => 'Operations Staff', 'email' => 'staff@netpack.test', 'password' => 'Netpack!Staff#2026', 'user_type' => 'staff'],
            ['name' => 'Domestic Partner', 'email' => 'partner@netpack.test', 'password' => 'Netpack!Partner#2026', 'user_type' => 'partner'],
            ['name' => 'Overseas Partner', 'email' => 'overseas@netpack.test', 'password' => 'Netpack!Overseas#2026', 'user_type' => 'overseas'],
            ['name' => 'E-commerce Seller', 'email' => 'seller@netpack.test', 'password' => 'Netpack!Seller#2026', 'user_type' => 'seller'],
            ['name' => 'Test Seller', 'email' => 'seller@test.com', 'password' => 'Netpack!Seller#2026', 'user_type' => 'seller'],
            ['name' => 'Delivery Rider', 'email' => 'rider@netpack.test', 'password' => 'Netpack!Rider#2026', 'user_type' => 'rider'],
            ['name' => 'Test Rider', 'email' => 'rider@test.com', 'password' => 'Netpack!Rider#2026', 'user_type' => 'rider'],
            ['name' => 'Customer', 'email' => 'customer@netpack.test', 'password' => 'Netpack!Customer#2026', 'user_type' => 'customer'],
            ['name' => 'Business Client', 'email' => 'client@netpack.test', 'password' => 'Netpack!Client#2026', 'user_type' => 'client'],
        ];

        foreach ($accounts as $account) {
            $user = User::updateOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'password' => Hash::make($account['password']),
                    'user_type' => $account['user_type'],
                    'verification_status' => 'approved',
                    'registration_completed' => true,
                    'password_changed' => true,
                    'is_online' => true,
                    'is_available' => true,
                ]
            );

            // Ensure wallet exists for financial accounts
            $walletUserType = match ($user->user_type) {
                'seller' => 'seller',
                'rider' => 'rider',
                'customer', 'client' => 'customer',
                default => 'admin',
            };

            Wallet::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'user_type' => $walletUserType,
                    'balance' => in_array($user->user_type, ['seller', 'client', 'partner']) ? 15000.00 : 0.00,
                ]
            );

            // Ensure verified rider profile exists for riders
            if ($user->user_type === 'rider') {
                RiderProfile::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'rider_code' => 'RDR-' . str_pad($user->id, 4, '0', STR_PAD_LEFT),
                        'full_name' => $user->name,
                        'email' => $user->email,
                        'mobile' => $user->phone ?? ('980000000' . ($user->id % 10)),
                        'vehicle_type' => 'motorcycle',
                        'vehicle_number' => 'BA-99-PA-1234',
                        'verification_status' => 'verified',
                        'verified_at' => now(),
                        'availability_status' => 'online',
                        'cod_level' => 'level_3',
                        'cod_limit' => 50000.00,
                        'current_outstanding_cod' => 0.00,
                        'trust_score' => 100,
                        'rating' => 5.0,
                    ]
                );
            }
        }

        $this->command?->info('Local demo accounts seeded. Temporary credentials are documented in README.md.');
    }
}
