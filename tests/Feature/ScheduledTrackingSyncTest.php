<?php

namespace Tests\Feature;

use App\Models\MAWB;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScheduledTrackingSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_artisan_tracking_sync_all_command_executes_successfully(): void
    {
        $admin = User::factory()->create(['user_type' => User::TYPE_SUPER_ADMIN]);
        $client = User::factory()->create(['user_type' => User::TYPE_CLIENT]);

        // 1. Create an active MAWB
        MAWB::create([
            'mawb_number' => '157-11223344',
            'airline_name' => 'Qatar Airways Cargo',
            'airline_code' => 'QR',
            'origin_airport' => 'KTM',
            'destination_airport' => 'DOH',
            'flight_number' => 'QR645',
            'flight_date' => now()->subHours(1),
            'status' => 'assigned',
            'created_by' => $admin->id,
        ]);

        // 2. Create an active international shipment with last-mile tracking number
        Shipment::create([
            'hawb_number' => 'HAWB-CRON-01',
            'tracking_number' => 'NP-CRON-01',
            'customer_id' => $client->id,
            'shipment_type' => 'international',
            'service_type' => 'express',
            'sender_name' => 'Nepal Craft Exporter',
            'sender_phone' => '9800000000',
            'sender_address' => 'Thamel',
            'sender_city' => 'Kathmandu',
            'receiver_name' => 'Tokyo Buyer',
            'receiver_phone' => '+81-3-0000-0000',
            'receiver_address' => 'Ginza',
            'receiver_city' => 'Tokyo',
            'receiver_country' => 'Japan',
            'actual_weight' => 1.5,
            'chargeable_weight' => 1.5,
            'shipping_cost' => 4500,
            'total_amount' => 4500,
            'status' => 'in_transit',
            'last_mile_carrier_name' => 'DHL',
            'last_mile_tracking_number' => '1234567890',
        ]);

        // Run the console command
        $this->artisan('tracking:sync-all')->assertSuccessful();
    }
}
