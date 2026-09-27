<?php

namespace Tests\Feature;

use App\Models\Agency;
use App\Models\OverseasHub;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InternationalHubMultiPartnerAndSuperAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_super_admin_can_access_international_hubs_index_and_create(): void
    {
        $superAdmin = User::factory()->create([
            'user_type' => 'super_admin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($superAdmin)->get(route('international.hubs.index'));
        $response->assertStatus(200);
        $response->assertSee('International Hubs');

        $createResponse = $this->actingAs($superAdmin)->get(route('international.hubs.create'));
        $createResponse->assertStatus(200);
        $createResponse->assertSee('Create International Gateway Hub');
        $createResponse->assertSee('Main Delivery Areas');
        $createResponse->assertSee('Transit Services');
    }

    public function test_auto_coverage_api_returns_structured_delivery_and_transit_areas(): void
    {
        $admin = User::factory()->create([
            'user_type' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->getJson(route('international.hubs.auto-coverage', [
            'country' => 'United Arab Emirates',
            'code' => 'DXB',
        ]));

        $response->assertStatus(200);
        $response->assertJsonPath('hub_code', 'DXB');
        $response->assertJsonPath('country', 'United Arab Emirates');
        $data = $response->json();
        $this->assertContains('United Arab Emirates', $data['main_delivery_countries']);
        $this->assertContains('Saudi Arabia', $data['main_delivery_countries']);
        $this->assertContains('United States', $data['transit_countries']);
    }

    public function test_hub_can_have_multiple_partners_and_partners_can_serve_multiple_hubs(): void
    {
        $admin = User::factory()->create([
            'user_type' => 'super_admin',
            'is_active' => true,
        ]);

        // Create 3 partner agencies
        $agencyA = Agency::create([
            'name' => 'Agency Alpha Global',
            'code' => 'ALPH-1',
            'country' => 'United Arab Emirates',
            'city' => 'Dubai',
            'address' => 'Airport Freezone',
            'phone' => '+971-4-1111111',
            'primary_contact' => 'Agent Alpha',
            'email' => 'alpha@agency.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $agencyB = Agency::create([
            'name' => 'Agency Beta Logistics',
            'code' => 'BETA-1',
            'country' => 'United Kingdom',
            'city' => 'London',
            'address' => 'Heathrow Cargo Centre',
            'phone' => '+44-20-2222222',
            'primary_contact' => 'Agent Beta',
            'email' => 'beta@agency.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        // Create Hub 1 with Agency A and Agency B
        $postData = [
            'hub_code' => 'DXB',
            'hub_name' => 'Dubai Air Cargo Gateway',
            'country' => 'United Arab Emirates',
            'location' => 'Dubai',
            'airport_name' => 'Dubai International (DXB)',
            'hub_type' => 'main_hub',
            'mode_type' => 'DDP & Cross Worldwide',
            'main_delivery_countries' => 'United Arab Emirates, Saudi Arabia, Qatar, Oman',
            'transit_countries' => 'United States, United Kingdom, Canada, Australia',
            'service_routes' => 'GCC Door Delivery + Worldwide Transit',
            'partner_agency_ids' => [$agencyA->id, $agencyB->id],
            'is_active' => '1',
        ];

        $res = $this->actingAs($admin)->post(route('international.hubs.store'), $postData);
        $res->assertRedirect(route('international.hubs.index'));

        $hubDxb = OverseasHub::where('hub_code', 'DXB')->first();
        $this->assertNotNull($hubDxb);
        $this->assertCount(2, $hubDxb->agencies);
        $this->assertContains('Saudi Arabia', $hubDxb->main_delivery_countries);
        $this->assertContains('United States', $hubDxb->transit_countries);

        // Create Hub 2 (London LHR) also using Agency A (same partner serving multiple hubs)
        $hubLhr = OverseasHub::create([
            'hub_code' => 'LHR',
            'hub_name' => 'London Heathrow Gateway',
            'country' => 'United Kingdom',
            'location' => 'London',
            'airport_name' => 'Heathrow (LHR)',
            'mode_type' => 'UK/EU DDP',
            'main_delivery_countries' => ['United Kingdom', 'Ireland'],
            'transit_countries' => ['France', 'Germany'],
            'is_active' => true,
        ]);
        $hubLhr->agencies()->attach([$agencyA->id]);

        $this->assertTrue($agencyA->hubs()->where('overseas_hubs.id', $hubDxb->id)->exists());
        $this->assertTrue($agencyA->hubs()->where('overseas_hubs.id', $hubLhr->id)->exists());
        $this->assertCount(2, $agencyA->hubs);
    }

    public function test_tracking_dynamically_resolves_hub_by_country_and_respects_admin_override(): void
    {
        // Hub 1: DXB covering UAE and Saudi Arabia as main delivery
        $hubDxb = OverseasHub::create([
            'hub_code' => 'DXB',
            'hub_name' => 'Dubai Gateway',
            'country' => 'United Arab Emirates',
            'location' => 'Dubai',
            'mode_type' => 'DDP',
            'main_delivery_countries' => ['United Arab Emirates', 'Saudi Arabia', 'Qatar'],
            'transit_countries' => ['Worldwide Transit'],
            'is_active' => true,
            'sort_order' => 1,
        ]);

        // Hub 2: LHR covering UK as main delivery
        $hubLhr = OverseasHub::create([
            'hub_code' => 'LHR',
            'hub_name' => 'Heathrow Gateway',
            'country' => 'United Kingdom',
            'location' => 'London',
            'mode_type' => 'DDP',
            'main_delivery_countries' => ['United Kingdom', 'Ireland'],
            'transit_countries' => ['Poland', 'Germany'],
            'is_active' => true,
            'sort_order' => 2,
        ]);

        $client = User::factory()->create([
            'user_type' => 'customer',
        ]);

        // Shipment bound for UK without manual hub
        $shipmentUk = Shipment::create([
            'customer_id' => $client->id,
            'user_id' => $client->id,
            'tracking_number' => 'NET-UK-9999',
            'sender_name' => 'Ram Shrestha',
            'sender_phone' => '9841000000',
            'sender_address' => 'Thamel',
            'sender_city' => 'Kathmandu',
            'sender_country' => 'Nepal',
            'receiver_name' => 'John Doe',
            'receiver_phone' => '+44-20-12345678',
            'receiver_address' => 'Oxford Street',
            'receiver_city' => 'London',
            'receiver_country' => 'United Kingdom',
            'weight' => 2.5,
            'status' => 'in_transit',
            'current_hub_id' => null, // Auto figure out
        ]);

        $resolved = OverseasHub::resolveHubForShipment($shipmentUk);
        $this->assertEquals('LHR', $resolved->hub_code);

        // Admin changes current_hub_id manually to DXB due to flight reroute
        $shipmentUk->current_hub_id = $hubDxb->id;
        $shipmentUk->save();

        $manualResolved = OverseasHub::resolveHubForShipment($shipmentUk);
        $this->assertEquals('DXB', $manualResolved->hub_code);

        // Public tracking page loads successfully with resolved hub
        $response = $this->get(route('tracking.public', ['tracking_number' => 'NET-UK-9999']));
        $response->assertStatus(200);
        $response->assertSee('DXB');
    }

    public function test_ai_assistance_suggestions_are_consistent_for_all_users(): void
    {
        $aiService = app(\App\Services\AiAssistantService::class);

        $clientUser = User::factory()->create(['user_type' => 'customer']);
        $adminUser = User::factory()->create(['user_type' => 'super_admin']);
        $guestUser = null;

        $clientSuggestions = $aiService->getQuickSuggestions($clientUser);
        $adminSuggestions = $aiService->getQuickSuggestions($adminUser);
        $guestSuggestions = $aiService->getQuickSuggestions($guestUser);

        $clientLabels = array_column($clientSuggestions, 'label');
        $adminLabels = array_column($adminSuggestions, 'label');
        $guestLabels = array_column($guestSuggestions, 'label');

        // Both client and admin receive the same core client-grade quick suggestions
        $this->assertNotEmpty(array_filter($clientLabels, fn($l) => str_contains($l, 'Track Consignment')));
        $this->assertNotEmpty(array_filter($adminLabels, fn($l) => str_contains($l, 'Track Consignment')));
        $this->assertNotEmpty(array_filter($guestLabels, fn($l) => str_contains($l, 'Track Consignment')));

        $this->assertNotEmpty(array_filter($clientLabels, fn($l) => str_contains($l, 'Calculate Delivery Rate')));
        $this->assertNotEmpty(array_filter($adminLabels, fn($l) => str_contains($l, 'Calculate Delivery Rate')));
    }
}
