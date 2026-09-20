<?php

namespace Tests\Feature;

use App\Models\MAWB;
use App\Models\OverseasHub;
use App\Models\Shipment;
use App\Models\User;
use App\Services\Tracking\MawbFlightTrackingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MawbFlightTrackingTest extends TestCase
{
    use RefreshDatabase;

    private MawbFlightTrackingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(MawbFlightTrackingService::class);
    }

    public function test_resolves_airline_from_mawb_prefix(): void
    {
        // 285 = Nepal Airlines
        $ra = $this->service->resolveAirlineByMawb('285-12345678');
        $this->assertEquals('Nepal Airlines', $ra['name']);
        $this->assertEquals('RA', $ra['code']);
        $this->assertEquals('KTM', $ra['hub']);
        $this->assertStringContainsString('track-trace', $ra['tracking_url']);

        // 157 = Qatar Airways
        $qr = $this->service->resolveAirlineByMawb('157-98765432');
        $this->assertEquals('Qatar Airways Cargo', $qr['name']);
        $this->assertEquals('QR', $qr['code']);
        $this->assertEquals('DOH', $qr['hub']);
        $this->assertStringContainsString('qrcargo.com', $qr['tracking_url']);

        // 176 = Emirates SkyCargo
        $ek = $this->service->resolveAirlineByMawb('176-55443322');
        $this->assertEquals('Emirates SkyCargo', $ek['name']);
        $this->assertEquals('EK', $ek['code']);
        $this->assertEquals('DXB', $ek['hub']);
        $this->assertStringContainsString('skycargo.com', $ek['tracking_url']);
    }

    public function test_mawb_flight_sync_advances_status_and_cascades_to_child_shipments(): void
    {
        $admin = User::factory()->create(['user_type' => User::TYPE_SUPER_ADMIN]);
        $client = User::factory()->create(['user_type' => User::TYPE_CLIENT]);

        $hub = OverseasHub::create([
            'hub_code' => 'DXB',
            'hub_name' => 'Dubai Gateway Transit Hub',
            'city' => 'Dubai',
            'country' => 'United Arab Emirates',
            'is_active' => true,
        ]);

        $mawb = MAWB::create([
            'mawb_number' => '176-88990011',
            'airline_name' => 'Emirates SkyCargo',
            'airline_code' => 'EK',
            'origin_airport' => 'KTM',
            'destination_airport' => 'DXB',
            'hub_id' => $hub->id,
            'flight_number' => 'EK2354',
            'flight_date' => now()->subHours(2),
            'status' => 'assigned',
            'created_by' => $admin->id,
        ]);

        $shipment = Shipment::create([
            'hawb_number' => 'HAWB-MAWB-SYNC-01',
            'tracking_number' => 'NP-MAWB-SYNC-01',
            'customer_id' => $client->id,
            'shipment_type' => 'international',
            'service_type' => 'standard',
            'sender_name' => 'Nepal Merchant',
            'sender_phone' => '9800000000',
            'sender_address' => 'Thamel',
            'sender_city' => 'Kathmandu',
            'sender_country' => 'Nepal',
            'receiver_name' => 'London Importer',
            'receiver_phone' => '+44-20-1234-5678',
            'receiver_address' => 'Oxford St',
            'receiver_city' => 'London',
            'receiver_country' => 'United Kingdom',
            'actual_weight' => 5.0,
            'chargeable_weight' => 5.0,
            'shipping_cost' => 8000,
            'total_amount' => 8000,
            'status' => 'confirmed',
            'mawb_id' => $mawb->id,
            'mawb_number' => $mawb->mawb_number,
        ]);

        // Sync the MAWB
        $result = $this->service->syncMawb($mawb);

        $this->assertTrue($result['success']);
        $this->assertTrue($result['cascaded']);

        $mawb->refresh();
        $this->assertEquals('in_transit', $mawb->status);

        // Check that child shipment was updated and received airline transit milestone
        $shipment->refresh();
        $this->assertEquals('in_transit', $shipment->status);
        $this->assertEquals('in_transit_airline', $shipment->agency_milestone);
        $this->assertNotEmpty($shipment->tracking_history);

        $history = $shipment->tracking_history;
        $latestEvent = end($history);
        $this->assertEquals('in_transit_airline', $latestEvent['event_code']);
        $this->assertStringContainsString('Emirates SkyCargo', $latestEvent['description']);
    }
}
