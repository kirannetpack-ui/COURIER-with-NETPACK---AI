<?php

namespace Database\Seeders;

use App\Models\User;
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
            ['name' => 'Delivery Rider', 'email' => 'rider@netpack.test', 'password' => 'Netpack!Rider#2026', 'user_type' => 'rider'],
            ['name' => 'Customer', 'email' => 'customer@netpack.test', 'password' => 'Netpack!Customer#2026', 'user_type' => 'customer'],
            ['name' => 'Business Client', 'email' => 'client@netpack.test', 'password' => 'Netpack!Client#2026', 'user_type' => 'client'],
        ];

        foreach ($accounts as $account) {
            User::updateOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'password' => Hash::make($account['password']),
                    'user_type' => $account['user_type'],
                    'verification_status' => 'approved',
                    'registration_completed' => true,
                    'password_changed' => true,
                ]
            );
        }

        $this->command?->info('Local demo accounts seeded. Temporary credentials are documented in README.md.');
    }
}
