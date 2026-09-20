<?php

namespace Tests\Feature;

use App\Models\Shipment;
use App\Models\User;
use App\Models\MAWB;
use App\Models\LastMileCarrier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTrackingConsoleTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $client;
    private Shipment $shipment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'user_type' => 'admin',
        ]);

        $this->client = User::factory()->create([
            'user_type' => User::TYPE_CLIENT,
            'name' => 'Himalayan Exports Ltd',
            'phone' => '9801122334',
        ]);

        $this->shipment = Shipment::create([
            'tracking_number' => 'NPI-TEST-CONSOLE-01',
            'hawb_number' => 'HAWB-TEST-CONSOLE-01',
            'customer_id' => $this->client->id,
            'shipment_type' => 'parcel',
            'service_type' => 'express',
            'sender_name' => 'Kathmandu Shipper',
            'sender_phone' => '9800000001',
            'sender_address' => 'Thamel',
            'sender_city' => 'Kathmandu',
            'receiver_name' => 'Robert Johnson',
            'receiver_phone' => '+1-415-555-0199',
            'receiver_address' => '100 Market St',
            'receiver_city' => 'San Francisco',
            'receiver_country' => 'United States',
            'actual_weight' => 3.2,
            'chargeable_weight' => 3.2,
            'shipping_cost' => 6000,
            'total_amount' => 6000,
            'status' => 'confirmed',
            'tracking_history' => [
                [
                    'time' => now()->subDay()->toIso8601String(),
                    'status' => 'confirmed',
                    'status_label' => 'Booking Confirmed',
                    'location' => 'Kathmandu Hub',
                    'description' => 'Initial booking registered',
                ],
            ],
        ]);
    }

    public function test_admin_can_view_tracking_operations_dashboard(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.tracking.index'));

        $response->assertStatus(200);
        $response->assertSee('Global Tracking');
        $response->assertSee($this->shipment->tracking_number);
        $response->assertSee('Himalayan Exports Ltd');
    }

    public function test_admin_can_view_comprehensive_tracking_entry_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.shipments.tracking', $this->shipment->id));

        $response->assertStatus(200);
        $response->assertSee('Operations Tracking');
        $response->assertSee($this->shipment->tracking_number);
        $response->assertSee('Himalayan Exports Ltd');
        $response->assertSee('Master Air Waybill (MAWB) Airline Allocation');
        $response->assertSee('Last-Mile Courier');
    }

    public function test_admin_can_assign_mawb_and_carrier_and_record_milestone(): void
    {
        $secondClient = User::factory()->create([
            'user_type' => User::TYPE_CLIENT,
            'name' => 'Nepal Artisans Collective',
        ]);

        $payload = [
            'customer_id' => $secondClient->id,
            'sender_name' => 'Updated Shipper Name',
            'receiver_name' => 'Robert Johnson (Direct)',
            'receiver_city' => 'San Francisco',
            'receiver_country' => 'United States',
            'mawb_selection_mode' => 'new',
            'new_mawb_number' => '157-98765432',
            'airline_name' => 'Qatar Airways Cargo',
            'flight_number' => 'QR651',
            'flight_date' => now()->format('Y-m-d\TH:i'),
            'origin_airport' => 'KTM - Tribhuvan International',
            'destination_airport' => 'DOH - Doha Cargo Hub',
            'last_mile_carrier_name' => 'DHL Express',
            'last_mile_tracking_number' => '9876543210',
            'record_scan' => 1,
            'milestone_event' => 'in_transit_airline',
            'event_date' => date('Y-m-d'),
            'event_time' => '14:30',
            'location' => 'TIA International Airport, Kathmandu',
            'description' => 'Cargo loaded on Qatar Airways flight QR651 to Doha Hub',
            'notify_client' => 0,
            'trigger_carrier_sync' => 0,
            'trigger_mawb_sync' => 0,
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.shipments.update-tracking', $this->shipment->id), $payload);

        $response->assertRedirect(route('admin.shipments.tracking', $this->shipment->id));
        $response->assertSessionHas('success');

        $this->shipment->refresh();

        // Verify Client Association
        $this->assertEquals($secondClient->id, $this->shipment->customer_id);

        // Verify MAWB Allocation
        $this->assertEquals('157-98765432', $this->shipment->mawb_number);
        $this->assertNotNull($this->shipment->mawb_id);

        $mawb = MAWB::find($this->shipment->mawb_id);
        $this->assertNotNull($mawb);
        $this->assertEquals('157-98765432', $mawb->mawb_number);
        $this->assertEquals('QR651', $mawb->flight_number);

        // Verify Last-Mile Carrier Allocation
        $this->assertEquals('DHL Express', $this->shipment->last_mile_carrier_name);
        $this->assertEquals('9876543210', $this->shipment->last_mile_tracking_number);

        // Verify Milestone Logged
        $history = $this->shipment->tracking_history;
        $latest = end($history);
        $this->assertEquals('in_transit_airline', $latest['event_code'] ?? ($latest['status'] ?? ''));
        $this->assertStringContainsString('TIA International Airport', $latest['location']);
        $this->assertEquals('in_transit', $this->shipment->status);
    }

    public function test_admin_can_assign_existing_mawb(): void
    {
        $existingMawb = MAWB::create([
            'mawb_number' => '176-12345678',
            'airline_name' => 'Emirates SkyCargo',
            'airline_code' => 'EK',
            'flight_number' => 'EK2355',
            'flight_date' => now(),
            'origin_airport' => 'KTM',
            'destination_airport' => 'DXB',
            'status' => 'active',
        ]);

        $payload = [
            'mawb_selection_mode' => 'existing',
            'existing_mawb_id' => $existingMawb->id,
            'last_mile_carrier_name' => 'FedEx',
            'last_mile_tracking_number' => '794612345678',
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.shipments.update-tracking', $this->shipment->id), $payload);

        $response->assertRedirect(route('admin.shipments.tracking', $this->shipment->id));

        $this->shipment->refresh();
        $this->assertEquals($existingMawb->id, $this->shipment->mawb_id);
        $this->assertEquals('176-12345678', $this->shipment->mawb_number);
        $this->assertEquals('FedEx', $this->shipment->last_mile_carrier_name);
        $this->assertEquals('794612345678', $this->shipment->last_mile_tracking_number);
    }
}
