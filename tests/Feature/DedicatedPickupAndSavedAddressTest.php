<?php

namespace Tests\Feature;

use App\Models\PickupRequest;
use App\Models\SavedAddress;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DedicatedPickupAndSavedAddressTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed basic logistics and roles if needed
    }

    public function test_customer_sidebar_includes_unified_create_shipment_and_no_redundant_pickup_menu()
    {
        $client = User::factory()->create([
            'user_type' => User::TYPE_CLIENT,
            'email' => 'client_nav@test.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->actingAs($client)->get(route('client.dashboard'));

        $response->assertStatus(200);
        $response->assertSee(route('shipments.create'), false);
        $response->assertSee('Create Shipment');
        // Redundant separate Request Pickup link removed in favor of single unified creation console
        $response->assertDontSee(route('client.inquiries'), false);
    }

    public function test_client_can_submit_pickup_without_mandatory_destination()
    {
        $client = User::factory()->create([
            'user_type' => User::TYPE_CLIENT,
            'name' => 'Kiran Nepali',
            'phone' => '9841223344',
            'address' => 'Ward 4, Baluwatar, Kathmandu',
        ]);

        $response = $this->actingAs($client)->post(route('client.inquiries.store'), [
            'contact_person_name' => 'Kiran Nepali',
            'contact_phone' => '9841223344',
            'pickup_address' => 'Baluwatar Ward 4, Near PM Quarters',
            'pickup_landmark' => 'Near Russian Embassy',
            'pickup_city' => 'Kathmandu',
            'destination_scope' => 'inside_valley',
            // Notice: delivery_address, recipient_name, recipient_phone, delivery_city are completely omitted!
            'package_type' => 'Parcel & Commercial Goods',
            'estimated_weight_kg' => 2.5,
            'save_address' => 1,
            'address_label' => 'Baluwatar HQ',
        ]);

        $response->assertRedirect(route('client.inquiries'));
        $response->assertSessionHas('success');

        // Verify pickup request was created in DB with null/open destination
        $this->assertDatabaseHas('pickup_requests', [
            'seller_id' => $client->id,
            'contact_person_name' => 'Kiran Nepali',
            'contact_person_phone' => '9841223344',
            'delivery_address' => null,
            'service_tier' => 'flash', // Inside Valley defaults to E-Commerce Instant Dispatch / Flash
            'status' => 'pending',
        ]);

        // Verify address was auto-saved to saved_addresses table
        $this->assertDatabaseHas('saved_addresses', [
            'user_id' => $client->id,
            'contact_person_name' => 'Kiran Nepali',
            'contact_person_phone' => '9841223344',
            'address' => 'Baluwatar Ward 4, Near PM Quarters',
            'label' => 'Baluwatar HQ',
        ]);
    }

    public function test_saved_addresses_are_reused_and_usage_count_increments()
    {
        $client = User::factory()->create([
            'user_type' => User::TYPE_CLIENT,
        ]);

        $savedAddress = SavedAddress::create([
            'user_id' => $client->id,
            'label' => 'Warehouse A',
            'contact_person_name' => 'Warehouse Manager',
            'contact_person_phone' => '9801122334',
            'address' => 'Tinkune Industrial Area',
            'city' => 'Kathmandu',
            'is_default' => true,
            'usage_count' => 1,
        ]);

        // Submit pickup with this saved address
        $response = $this->actingAs($client)->post(route('client.inquiries.store'), [
            'contact_person_name' => 'Warehouse Manager',
            'contact_phone' => '9801122334',
            'pickup_address' => 'Tinkune Industrial Area',
            'destination_scope' => 'inside_valley',
            'package_type' => 'Legal & Business Documents',
            'estimated_weight_kg' => 1.0,
            'save_address' => 1,
        ]);

        $response->assertRedirect(route('client.inquiries'));

        $savedAddress->refresh();
        $this->assertEquals(2, $savedAddress->usage_count);
        $this->assertNotNull($savedAddress->last_used_at);
    }

    public function test_client_can_optionally_provide_destination_details()
    {
        $client = User::factory()->create([
            'user_type' => User::TYPE_CLIENT,
        ]);

        $response = $this->actingAs($client)->post(route('client.inquiries.store'), [
            'contact_person_name' => 'Sender Representative',
            'contact_phone' => '9841000000',
            'pickup_address' => 'Thamel Marg 12',
            'destination_scope' => 'outside_valley',
            'delivery_address' => 'Lakeside Pokhara Ward 6',
            'delivery_city' => 'Pokhara',
            'recipient_name' => 'Hotel General Manager',
            'recipient_phone' => '9800000001',
            'package_type' => 'Parcel & Commercial Goods',
            'estimated_weight_kg' => 3.0,
            'service_tier' => 'express',
        ]);

        $response->assertRedirect(route('client.inquiries'));

        $this->assertDatabaseHas('pickup_requests', [
            'seller_id' => $client->id,
            'pickup_address' => 'Thamel Marg 12',
            'delivery_address' => 'Lakeside Pokhara Ward 6',
            'delivery_city' => 'Pokhara',
            'customer_name' => 'Hotel General Manager',
            'customer_phone' => '9800000001',
            'service_tier' => 'express',
        ]);
    }
}
