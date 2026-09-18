<?php

namespace Tests\Feature;

use App\Models\DeliveryZone;
use App\Models\PickupRequest;
use App\Models\SavedAddress;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShipmentCreatePickupIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_shipment_create_page_renders_saved_addresses_and_recent_pickups()
    {
        $client = User::factory()->create([
            'user_type' => User::TYPE_CLIENT,
            'name' => 'Dipak Thapa',
            'email' => 'dipak@test.com',
            'phone' => '9841998877',
        ]);

        $savedAddr = SavedAddress::create([
            'user_id' => $client->id,
            'label' => 'Central Warehouse',
            'contact_person_name' => 'Dipak Thapa',
            'contact_person_phone' => '9841998877',
            'address' => 'Tinkune Plot 4, Kathmandu',
            'city' => 'Kathmandu',
            'is_default' => true,
        ]);

        $pickupInquiry = PickupRequest::create([
            'seller_id' => $client->id,
            'contact_person_name' => 'Dipak Thapa',
            'contact_person_phone' => '9841998877',
            'pickup_address' => 'Baneshwor Marg 1',
            'delivery_address' => 'Lalitpur Ward 3',
            'items_description' => 'Garments Samples',
            'estimated_weight_kg' => 2.0,
            'scheduled_pickup_time' => now()->addDay(),
            'status' => 'pending',
        ]);

        $response = $this->actingAs($client)->get(route('shipments.create'));

        $response->assertStatus(200);
        $response->assertSee('Central Warehouse');
        $response->assertSee('Tinkune Plot 4, Kathmandu');
        $response->assertSee($pickupInquiry->tracking_number);
        $response->assertSee('leaflet.js', false);
        $response->assertSee('leaflet.css', false);
    }

    public function test_can_submit_domestic_shipment_with_multiple_pickups_and_deliveries()
    {
        $client = User::factory()->create([
            'user_type' => User::TYPE_CLIENT,
        ]);

        $zoneA = DeliveryZone::create([
            'zone_name' => 'Kathmandu Metro',
            'zone_code' => 'KTM-01',
            'district' => 'Kathmandu',
            'province' => 'Bagmati',
            'approval_status' => 'approved',
            'is_active' => true,
        ]);

        $zoneB = DeliveryZone::create([
            'zone_name' => 'Pokhara Hub',
            'zone_code' => 'PKR-01',
            'district' => 'Kaski',
            'province' => 'Gandaki',
            'approval_status' => 'approved',
            'is_active' => true,
        ]);

        $partner = User::factory()->create([
            'user_type' => User::TYPE_PARTNER,
        ]);

        \App\Models\DomesticRate::create([
            'partner_id' => $partner->id,
            'origin_zone_id' => $zoneA->id,
            'destination_zone_id' => $zoneB->id,
            'origin_city' => 'Kathmandu',
            'destination_city' => 'Pokhara',
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

        $response = $this->actingAs($client)->post(route('shipments.store'), [
            'shipment_type' => 'domestic',
            'service_type' => 'standard',
            'origin_zone_id' => $zoneA->id,
            'destination_zone_id' => $zoneB->id,
            'weight' => 3.5,
            'pickup_name' => ['Warehouse North', 'Store Branch South'],
            'pickup_phone' => ['9841111111', '9842222222'],
            'pickup_address' => ['Baluwatar Ward 4, Kathmandu', 'Thamel Marg 12, Kathmandu'],
            'delivery_name' => ['Lakeside Customer', 'Old Bazaar Retail'],
            'delivery_phone' => ['9801111111', '9802222222'],
            'delivery_address' => ['Lakeside Ward 6, Pokhara', 'Mahendrapool, Pokhara'],
            'delivery_district' => ['Kaski', 'Kaski'],
            'delivery_province' => ['Gandaki', 'Gandaki'],
            'save_pickup_addresses' => 1,
        ]);

        $response->assertRedirect();

        $shipment = Shipment::latest()->first();
        $this->assertNotNull($shipment);
        $this->assertEquals('domestic', $shipment->shipment_type);

        // Verify multiple pickup points were stored
        $this->assertCount(2, $shipment->pickup_points);
        $this->assertEquals('Warehouse North', $shipment->pickup_points[0]['name']);
        $this->assertEquals('Store Branch South', $shipment->pickup_points[1]['name']);

        // Verify multiple delivery points were stored
        $this->assertCount(2, $shipment->delivery_points);
        $this->assertEquals('Lakeside Customer', $shipment->delivery_points[0]['name']);
        $this->assertEquals('Old Bazaar Retail', $shipment->delivery_points[1]['name']);

        // Verify pickup addresses were auto-saved
        $this->assertDatabaseHas('saved_addresses', [
            'user_id' => $client->id,
            'address' => 'Baluwatar Ward 4, Kathmandu',
        ]);
        $this->assertDatabaseHas('saved_addresses', [
            'user_id' => $client->id,
            'address' => 'Thamel Marg 12, Kathmandu',
        ]);
    }

    public function test_can_submit_international_shipment_with_multiple_pickups()
    {
        $client = User::factory()->create([
            'user_type' => User::TYPE_CLIENT,
        ]);

        $response = $this->actingAs($client)->post(route('shipments.store'), [
            'shipment_type' => 'international',
            'service_type' => 'express',
            'weight' => 4.0,
            'pickup_name' => ['Kathmandu Export Hub', 'Factory Annex'],
            'pickup_phone' => ['9841000000', '9842000000'],
            'pickup_address' => ['Gairidhara Ward 2, Kathmandu', 'Patan Industrial Estate'],
            'receiver_name' => 'John Doe Exports Ltd',
            'receiver_street' => '350 Fifth Ave, Suite 2100',
            'receiver_city' => 'New York',
            'receiver_state' => 'NY',
            'receiver_postal_code' => '10118',
            'receiver_country' => 'United States',
        ]);

        $response->assertRedirect();

        $shipment = Shipment::latest()->first();
        $this->assertNotNull($shipment);
        $this->assertEquals('international', $shipment->shipment_type);

        // Verify multiple pickup points preserved for international
        $this->assertCount(2, $shipment->pickup_points);
        $this->assertEquals('Kathmandu Export Hub', $shipment->pickup_points[0]['name']);
        $this->assertEquals('Factory Annex', $shipment->pickup_points[1]['name']);

        // Verify receiver
        $this->assertEquals('John Doe Exports Ltd', $shipment->receiver_name);
        $this->assertEquals('United States', $shipment->receiver_country);
    }
}
