<?php

namespace Tests\Feature;

use App\Models\DeliveryZone;
use App\Models\PickupRequest;
use App\Models\SavedAddress;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnifiedOperatingConsoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_can_access_unified_operating_console()
    {
        $client = User::factory()->create([
            'user_type' => User::TYPE_CLIENT,
            'name' => 'Prabhat Sharma',
            'email' => 'prabhat@example.com',
            'phone' => '9851000000',
        ]);

        $savedAddr = SavedAddress::create([
            'user_id' => $client->id,
            'label' => 'Main Office Hub',
            'contact_person_name' => 'Prabhat Sharma',
            'contact_person_phone' => '9851000000',
            'address' => 'Baluwatar Plot 10, Kathmandu',
            'city' => 'Kathmandu',
            'is_default' => true,
        ]);

        $response = $this->actingAs($client)->get(route('shipments.create'));

        $response->assertStatus(200);
        $response->assertSee('Ship & Pickup Console');
        $response->assertSee('Create Shipment & Pickup', false);
        $response->assertSee('Live Dispatches Queue');
        $response->assertSee('Main Office Hub');
        $response->assertSee('Baluwatar Plot 10, Kathmandu');
        $response->assertSee('Doorstep Courier Collection');
        $response->assertDontSee('ORIGIN TERRITORY & DESTINATION GATEWAY ROUTE');
        $response->assertSee('leaflet.js', false);
        $response->assertSee('leaflet.css', false);
    }

    public function test_client_accesses_single_unified_function_with_pickup_option_initially_enabled()
    {
        $client = User::factory()->create([
            'user_type' => User::TYPE_CLIENT,
        ]);

        $response = $this->actingAs($client)->get(route('shipments.create'));

        $response->assertStatus(200);
        $response->assertSee('Create Shipment & Pickup', false);
        $response->assertSee('schedule_doorstep_pickup');
        $response->assertSee('Doorstep Courier Collection');
        $response->assertSee('Station / Counter Drop-off');
        // Ensure Origin Territory & Destination Gateway Route is removed from client view
        $response->assertDontSee('ORIGIN TERRITORY & DESTINATION GATEWAY ROUTE');
        $response->assertDontSee('Select origin territory');
    }

    public function test_client_can_submit_consignment_with_automated_doorstep_pickup_rider_dispatch()
    {
        $client = User::factory()->create([
            'user_type' => User::TYPE_CLIENT,
            'name' => 'Dipendra Shah',
            'phone' => '9841999999',
        ]);

        $zoneOrigin = DeliveryZone::create([
            'zone_name' => 'Kathmandu Core',
            'zone_code' => 'KTM-CORE',
            'district' => 'Kathmandu',
            'province' => 'Bagmati',
            'approval_status' => 'approved',
            'is_active' => true,
        ]);

        $zoneDest = DeliveryZone::create([
            'zone_name' => 'Pokhara Central',
            'zone_code' => 'PKR-CENTRAL',
            'district' => 'Kaski',
            'province' => 'Gandaki',
            'approval_status' => 'approved',
            'is_active' => true,
        ]);

        $partner = User::factory()->create(['user_type' => User::TYPE_PARTNER]);

        \App\Models\DomesticRate::create([
            'partner_id' => $partner->id,
            'origin_zone_id' => $zoneOrigin->id,
            'destination_zone_id' => $zoneDest->id,
            'origin_city' => 'Kathmandu',
            'destination_city' => 'Pokhara',
            'service_type' => 'standard',
            'service_name' => 'STANDARD',
            'weight_from' => 0,
            'weight_to' => 10,
            'base_rate' => 120,
            'per_kg_rate' => 25,
            'rate_per_kg' => 25,
            'rate_type' => 'door_to_door',
            'approval_status' => 'approved',
            'effective_from' => now()->subDay(),
            'is_active' => true,
        ]);

        $pickupTime = now()->addHours(3)->format('Y-m-d H:i:s');

        $response = $this->actingAs($client)->post(route('shipments.store'), [
            'shipment_type' => 'domestic',
            'service_type' => 'standard',
            'origin_zone_id' => $zoneOrigin->id,
            'destination_zone_id' => $zoneDest->id,
            'weight' => 2.5,
            'pickup_name' => ['Kathmandu Central Store'],
            'pickup_phone' => ['9841999999'],
            'pickup_address' => ['Tripureshwor Ward 11, Kathmandu'],
            'delivery_name' => ['Pokhara Retailer'],
            'delivery_phone' => ['9801234567'],
            'delivery_address' => ['Lakeside Ward 6, Pokhara'],
            'schedule_doorstep_pickup' => 1,
            'scheduled_pickup_time' => $pickupTime,
            'save_pickup_addresses' => 1,
        ]);

        $response->assertRedirect();

        $shipment = Shipment::latest()->first();
        $this->assertNotNull($shipment);
        $this->assertEquals('domestic', $shipment->shipment_type);

        // Verify automated linked PickupRequest was created
        $pickup = PickupRequest::where('shipment_id', $shipment->id)->first();
        $this->assertNotNull($pickup, 'PickupRequest should have been automatically scheduled and linked to the shipment');
        $this->assertEquals($client->id, $pickup->seller_id);
        $this->assertEquals('Kathmandu Central Store', $pickup->contact_person_name);
        $this->assertEquals('9841999999', $pickup->contact_person_phone);
        $this->assertEquals('Tripureshwor Ward 11, Kathmandu', $pickup->pickup_address);
        $this->assertTrue(in_array($pickup->status, ['pending', 'assigned']));

        // Verify Eloquent relationship works in both directions
        $this->assertEquals($pickup->id, $shipment->pickupRequest->id);
        $this->assertEquals($shipment->id, $pickup->shipment->id);
    }

    public function test_client_can_upgrade_pickup_inquiry_to_full_consignment()
    {
        $client = User::factory()->create([
            'user_type' => User::TYPE_CLIENT,
        ]);

        $zoneA = DeliveryZone::create([
            'zone_name' => 'Kathmandu Metro',
            'zone_code' => 'KTM-METRO',
            'district' => 'Kathmandu',
            'province' => 'Bagmati',
            'approval_status' => 'approved',
            'is_active' => true,
        ]);

        $zoneB = DeliveryZone::create([
            'zone_name' => 'Biratnagar Hub',
            'zone_code' => 'BRT-HUB',
            'district' => 'Morang',
            'province' => 'Koshi',
            'approval_status' => 'approved',
            'is_active' => true,
        ]);

        $partner = User::factory()->create(['user_type' => User::TYPE_PARTNER]);

        \App\Models\DomesticRate::create([
            'partner_id' => $partner->id,
            'origin_zone_id' => $zoneA->id,
            'destination_zone_id' => $zoneB->id,
            'origin_city' => 'Kathmandu',
            'destination_city' => 'Biratnagar',
            'service_type' => 'standard',
            'service_name' => 'STANDARD',
            'weight_from' => 0,
            'weight_to' => 10,
            'base_rate' => 150,
            'per_kg_rate' => 30,
            'rate_per_kg' => 30,
            'rate_type' => 'door_to_door',
            'approval_status' => 'approved',
            'effective_from' => now()->subDay(),
            'is_active' => true,
        ]);

        // Prior doorstep pickup inquiry submitted by client
        $pickupInquiry = PickupRequest::create([
            'seller_id' => $client->id,
            'contact_person_name' => 'Sender Manager',
            'contact_person_phone' => '9801112233',
            'pickup_address' => 'Baneshwor Marg 4, Kathmandu',
            'estimated_weight_kg' => 4.0,
            'scheduled_pickup_time' => now()->addDay(),
            'status' => 'pending',
        ]);

        // Access conversion page
        $convResponse = $this->actingAs($client)->get(route('shipments.create', [
            'convert_pickup_id' => $pickupInquiry->id,
            'tab' => 'consignment'
        ]));

        $convResponse->assertStatus(200);
        $convResponse->assertSee('Upgrading Doorstep Collection to Full Consignment');
        $convResponse->assertSee($pickupInquiry->tracking_number);

        // Submit converted consignment
        $storeResponse = $this->actingAs($client)->post(route('shipments.store'), [
            'shipment_type' => 'domestic',
            'service_type' => 'standard',
            'origin_zone_id' => $zoneA->id,
            'destination_zone_id' => $zoneB->id,
            'weight' => 4.0,
            'pickup_name' => ['Sender Manager'],
            'pickup_phone' => ['9801112233'],
            'pickup_address' => ['Baneshwor Marg 4, Kathmandu'],
            'delivery_name' => ['Biratnagar Consignee'],
            'delivery_phone' => ['9802223344'],
            'delivery_address' => ['Main Bazaar, Biratnagar'],
            'convert_pickup_id' => $pickupInquiry->id,
        ]);

        $storeResponse->assertRedirect();

        $shipment = Shipment::latest()->first();
        $this->assertNotNull($shipment);

        $pickupInquiry->refresh();
        $this->assertEquals($shipment->id, $pickupInquiry->shipment_id, 'Existing pickup inquiry should be linked to the new shipment');
        $this->assertStringContainsString('Upgraded to Consignment', $pickupInquiry->status_notes);
    }

    public function test_sidebar_and_dashboard_render_unified_console_navigation()
    {
        $client = User::factory()->create([
            'user_type' => User::TYPE_CLIENT,
        ]);

        $dashboardResponse = $this->actingAs($client)->get(route('client.dashboard'));
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee(route('shipments.create'), false);
        $dashboardResponse->assertSee('Create Shipment');
        // Redundant separate Request Pickup link removed from customer navigation
        $dashboardResponse->assertDontSee(route('client.inquiries'), false);
        $dashboardResponse->assertSee('Ship & Pickup Console', false);

        // Dispatches Queue shortcut on dashboard
        $dashboardResponse->assertSee('Dispatches Queue');

        $inquiriesResponse = $this->actingAs($client)->get(route('client.inquiries'));
        $inquiriesResponse->assertStatus(200);
        $inquiriesResponse->assertSee('Full Consignment Console');
        $inquiriesResponse->assertSee('Submit New Consignment Pickup Inquiry');
    }

    public function test_client_can_submit_shipment_without_origin_and_destination_zones()
    {
        $client = User::factory()->create([
            'user_type' => User::TYPE_CLIENT,
            'name' => 'Suman Adhikari',
            'phone' => '9841123456',
        ]);

        $zoneOrigin = DeliveryZone::create([
            'zone_name' => 'Kathmandu Valley Hub',
            'zone_code' => 'KTM-VALLEY',
            'district' => 'Kathmandu',
            'province' => 'Bagmati',
            'approval_status' => 'approved',
            'is_active' => true,
        ]);

        $zoneDest = DeliveryZone::create([
            'zone_name' => 'Chitwan Hub',
            'zone_code' => 'CHITWAN-HUB',
            'district' => 'Chitwan',
            'province' => 'Bagmati',
            'approval_status' => 'approved',
            'is_active' => true,
        ]);

        $partner = User::factory()->create(['user_type' => User::TYPE_PARTNER]);

        \App\Models\DomesticRate::create([
            'partner_id' => $partner->id,
            'origin_zone_id' => $zoneOrigin->id,
            'destination_zone_id' => $zoneDest->id,
            'origin_city' => 'Kathmandu',
            'destination_city' => 'Chitwan',
            'service_type' => 'standard',
            'service_name' => 'STANDARD',
            'weight_from' => 0,
            'weight_to' => 10,
            'base_rate' => 100,
            'per_kg_rate' => 20,
            'rate_per_kg' => 20,
            'rate_type' => 'door_to_door',
            'approval_status' => 'approved',
            'effective_from' => now()->subDay(),
            'is_active' => true,
        ]);

        $pickupTime = now()->addHours(2)->format('Y-m-d H:i:s');

        // Post without origin_zone_id and destination_zone_id
        $response = $this->actingAs($client)->post(route('shipments.store'), [
            'shipment_type' => 'domestic',
            'service_type' => 'standard',
            'weight' => 1.5,
            'pickup_name' => ['Kathmandu Office'],
            'pickup_phone' => ['9841123456'],
            'pickup_address' => ['New Baneshwor, Kathmandu'],
            'delivery_name' => ['Narayangarh Store'],
            'delivery_phone' => ['9809988776'],
            'delivery_address' => ['Main Road, Narayangarh'],
            'schedule_doorstep_pickup' => 1,
            'scheduled_pickup_time' => $pickupTime,
        ]);

        $response->assertRedirect();

        $shipment = Shipment::latest()->first();
        $this->assertNotNull($shipment);
        $this->assertEquals('domestic', $shipment->shipment_type);

        $pickup = PickupRequest::where('shipment_id', $shipment->id)->first();
        $this->assertNotNull($pickup);
        $this->assertEquals($client->id, $pickup->seller_id);
        $this->assertEquals('New Baneshwor, Kathmandu', $pickup->pickup_address);
    }

    public function test_client_can_submit_self_dropoff_shipment_without_rider_pickup()
    {
        $client = User::factory()->create([
            'user_type' => User::TYPE_CLIENT,
        ]);

        $zoneOrigin = DeliveryZone::create([
            'zone_name' => 'Kathmandu Metro Hub',
            'zone_code' => 'KTM-METRO-2',
            'district' => 'Kathmandu',
            'province' => 'Bagmati',
            'approval_status' => 'approved',
            'is_active' => true,
        ]);

        $zoneDest = DeliveryZone::create([
            'zone_name' => 'Pokhara Hub',
            'zone_code' => 'PKR-HUB-2',
            'district' => 'Kaski',
            'province' => 'Gandaki',
            'approval_status' => 'approved',
            'is_active' => true,
        ]);

        $partner = User::factory()->create(['user_type' => User::TYPE_PARTNER]);

        \App\Models\DomesticRate::create([
            'partner_id' => $partner->id,
            'origin_zone_id' => $zoneOrigin->id,
            'destination_zone_id' => $zoneDest->id,
            'origin_city' => 'Kathmandu',
            'destination_city' => 'Pokhara',
            'service_type' => 'standard',
            'service_name' => 'STANDARD',
            'weight_from' => 0,
            'weight_to' => 10,
            'base_rate' => 120,
            'per_kg_rate' => 25,
            'rate_per_kg' => 25,
            'rate_type' => 'door_to_door',
            'approval_status' => 'approved',
            'effective_from' => now()->subDay(),
            'is_active' => true,
        ]);

        $response = $this->actingAs($client)->post(route('shipments.store'), [
            'shipment_type' => 'domestic',
            'service_type' => 'standard',
            'weight' => 2.0,
            'pickup_name' => ['Dropoff Shipper'],
            'pickup_phone' => ['9841000000'],
            'pickup_address' => ['Netpack Counter Putalisadak'],
            'delivery_name' => ['Dropoff Consignee'],
            'delivery_phone' => ['9802000000'],
            'delivery_address' => ['Pokhara Center'],
            'schedule_doorstep_pickup' => 0, // Unchecked by client
        ]);

        $response->assertRedirect();

        $shipment = Shipment::latest()->first();
        $this->assertNotNull($shipment);

        // No PickupRequest should be created when schedule_doorstep_pickup is 0
        $pickup = PickupRequest::where('shipment_id', $shipment->id)->first();
        $this->assertNull($pickup);
    }

    public function test_client_can_submit_self_dropoff_without_pickup_location_inputs()
    {
        $client = User::factory()->create([
            'user_type' => User::TYPE_CLIENT,
            'name' => 'Profile Sender',
            'phone' => '9841555666',
            'address' => 'Thamel Ward 26, Kathmandu',
        ]);

        $zoneOrigin = DeliveryZone::create([
            'zone_name' => 'KTM Center',
            'zone_code' => 'KTM-CTR',
            'district' => 'Kathmandu',
            'province' => 'Bagmati',
            'approval_status' => 'approved',
            'is_active' => true,
        ]);

        $zoneDest = DeliveryZone::create([
            'zone_name' => 'Butwal Hub',
            'zone_code' => 'BTW-HUB',
            'district' => 'Rupandehi',
            'province' => 'Lumbini',
            'approval_status' => 'approved',
            'is_active' => true,
        ]);

        $partner = User::factory()->create(['user_type' => User::TYPE_PARTNER]);

        \App\Models\DomesticRate::create([
            'partner_id' => $partner->id,
            'origin_zone_id' => $zoneOrigin->id,
            'destination_zone_id' => $zoneDest->id,
            'origin_city' => 'Kathmandu',
            'destination_city' => 'Butwal',
            'service_type' => 'standard',
            'service_name' => 'STANDARD',
            'weight_from' => 0,
            'weight_to' => 10,
            'base_rate' => 110,
            'per_kg_rate' => 20,
            'rate_per_kg' => 20,
            'rate_type' => 'door_to_door',
            'approval_status' => 'approved',
            'effective_from' => now()->subDay(),
            'is_active' => true,
        ]);

        // Submit with schedule_doorstep_pickup = 0 and NO pickup_name, pickup_phone, pickup_address
        $response = $this->actingAs($client)->post(route('shipments.store'), [
            'shipment_type' => 'domestic',
            'service_type' => 'standard',
            'origin_zone_id' => $zoneOrigin->id,
            'destination_zone_id' => $zoneDest->id,
            'weight' => 1.0,
            'delivery_name' => ['Butwal Recipient'],
            'delivery_phone' => ['9801234567'],
            'delivery_address' => ['Traffic Chowk, Butwal'],
            'schedule_doorstep_pickup' => 0,
            'sender_name' => 'Profile Sender',
            'sender_phone' => '9841555666',
            'sender_address' => 'Thamel Ward 26, Kathmandu',
        ]);

        $response->assertRedirect();

        $shipment = Shipment::latest()->first();
        $this->assertNotNull($shipment);
        $this->assertEquals('Profile Sender', $shipment->sender_name);
        $this->assertEquals('9841555666', $shipment->sender_phone);
        $this->assertEquals('Thamel Ward 26, Kathmandu', $shipment->sender_address);

        // Verify no rider pickup inquiry was created
        $pickup = PickupRequest::where('shipment_id', $shipment->id)->first();
        $this->assertNull($pickup);
    }
}
