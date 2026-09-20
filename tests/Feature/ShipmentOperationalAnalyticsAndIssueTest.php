<?php

namespace Tests\Feature;

use App\Models\DeliveryZone;
use App\Models\HsCode;
use App\Models\Shipment;
use App\Models\ShipmentIssue;
use App\Models\User;
use Database\Seeders\HsCodeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ShipmentOperationalAnalyticsAndIssueTest extends TestCase
{
    use RefreshDatabase;

    protected User $client;
    protected User $admin;
    protected DeliveryZone $ktmZone;
    protected DeliveryZone $pkrZone;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(HsCodeSeeder::class);

        $this->client = User::factory()->create([
            'user_type' => User::TYPE_CLIENT,
            'name' => 'Kathmandu Handicrafts Trade',
            'email' => 'client@handicrafts.com',
            'phone' => '9851099887',
        ]);

        $this->admin = User::factory()->create([
            'user_type' => User::TYPE_SUPER_ADMIN,
            'name' => 'System Admin',
            'email' => 'admin@netpack.com',
        ]);

        $this->ktmZone = DeliveryZone::create([
            'zone_code' => 'KTM-METRO',
            'zone_name' => 'Kathmandu Metro Hub',
            'district' => 'Kathmandu',
            'is_active' => true,
            'approval_status' => 'approved',
        ]);

        $this->pkrZone = DeliveryZone::create([
            'zone_code' => 'PKR-METRO',
            'zone_name' => 'Pokhara Hub',
            'district' => 'Kaski',
            'is_active' => true,
            'approval_status' => 'approved',
        ]);

        $partner = User::factory()->create(['user_type' => User::TYPE_PARTNER]);

        \App\Models\DomesticRate::create([
            'partner_id' => $partner->id,
            'origin_zone_id' => $this->ktmZone->id,
            'destination_zone_id' => $this->pkrZone->id,
            'origin_city' => 'Kathmandu',
            'destination_city' => 'Pokhara',
            'service_type' => 'standard',
            'service_name' => 'STANDARD',
            'weight_from' => 0,
            'weight_to' => 50,
            'base_rate' => 120,
            'per_kg_rate' => 25,
            'rate_per_kg' => 25,
            'rate_type' => 'door_to_door',
            'approval_status' => 'approved',
            'effective_from' => now()->subDay(),
            'is_active' => true,
        ]);
    }

    public function test_shipment_create_page_renders_columnar_invoice_table_and_acquisition_source(): void
    {
        $response = $this->actingAs($this->client)->get(route('shipments.create', [
            'shipment_type' => 'international',
        ]));

        $response->assertStatus(200);
        $response->assertSee('Declared Commodity Line Items (Commercial Invoice)');
        $response->assertSee('Acquisition Source');
        $response->assertSee('Direct Web Portal');
        $response->assertSee('Client Referral');
        $response->assertSee('Item / Commodity &amp; WCO HS Code', false);
        $response->assertSee('Add Commodity Row');
    }

    public function test_shipment_creation_records_18_operational_telemetry_metrics(): void
    {
        $invoiceData = [
            'invoice_number' => 'INV-2026-9901',
            'invoice_date' => '2026-09-20',
            'currency' => 'USD',
            'incoterm' => 'DAP',
            'export_reason' => 'Commercial Sale / Export',
            'acquisition_source' => 'referral',
            'shipper_pan_vat' => '600123456',
            'shipper_exim_code' => 'NP-EXIM-99',
            'subtotal' => 120.00,
            'items' => [
                [
                    'description' => 'Fine Cashmere Scarf',
                    'hs_code' => '6214.20.00',
                    'origin_country' => 'Nepal',
                    'quantity' => 4,
                    'uom' => 'PCS',
                    'unit_value' => 30.00,
                    'total_value' => 120.00,
                    'duty_rate' => 0,
                ]
            ]
        ];

        $packingListData = [
            'total_boxes' => 1,
            'total_gross_weight' => 2.5,
            'total_volumetric_weight' => 2.5,
            'boxes' => [
                [
                    'box_number' => 1,
                    'length_cm' => 35,
                    'width_cm' => 25,
                    'height_cm' => 15,
                    'weight_kg' => 2.5,
                    'items' => [
                        [
                            'item_index' => 0,
                            'item_name' => 'Fine Cashmere Scarf',
                            'qty' => 4,
                        ]
                    ]
                ]
            ]
        ];

        // 1st Shipment for this client
        $response1 = $this->actingAs($this->client)->post(route('shipments.store'), [
            'shipment_type' => 'international',
            'service_type' => 'standard',
            'weight' => 2.5,
            'origin_zone_id' => $this->ktmZone->id,
            'destination_zone_id' => $this->pkrZone->id,
            'sender_name' => $this->client->name,
            'sender_phone' => $this->client->phone,
            'sender_address' => 'Thamel, Kathmandu',
            'pickup_address' => ['Thamel, Kathmandu'],
            'pickup_name' => [$this->client->name],
            'pickup_phone' => [$this->client->phone],
            'receiver_name' => 'John Smith',
            'receiver_phone' => '+1-555-321-7788',
            'receiver_street' => '742 Evergreen Terrace',
            'receiver_city' => 'Springfield',
            'receiver_state' => 'OR',
            'receiver_postal_code' => '97477',
            'receiver_country' => 'United States',
            'schedule_doorstep_pickup' => '0',
            'acquisition_source' => 'referral',
            'cost_amount' => 1500.00,
            'invoice_data_json' => json_encode($invoiceData),
            'packing_list_data_json' => json_encode($packingListData),
        ]);

        $response1->assertSessionHasNoErrors();
        $response1->assertRedirect();

        $shipment1 = Shipment::where('receiver_name', 'John Smith')->first();
        $this->assertNotNull($shipment1);

        // Verify 18 Operational Telemetry Parameters on 1st shipment
        $this->assertEquals('Kathmandu', $shipment1->sender_city);
        $this->assertEquals('Nepal', $shipment1->sender_country);
        $this->assertEquals('Springfield', $shipment1->receiver_city);
        $this->assertEquals('United States', $shipment1->receiver_country);
        $this->assertEquals('United States', $shipment1->destination_country);
        $this->assertEquals(2.5, (float)$shipment1->actual_weight);
        $this->assertNotNull($shipment1->volumetric_weight);
        $this->assertEquals('international', $shipment1->shipment_type);
        $this->assertEquals('referral', $shipment1->acquisition_source);
        $this->assertGreaterThan(0, (float)$shipment1->price_quoted);
        $this->assertEquals((float)$shipment1->total_amount, (float)$shipment1->price_sold);
        $this->assertEquals(1500.00, (float)$shipment1->cost_amount);
        $this->assertEquals(round($shipment1->price_sold - 1500.00, 2), (float)$shipment1->gross_margin);
        $this->assertNotNull($shipment1->vendor_name);
        $this->assertFalse($shipment1->is_repeat_customer);
        $this->assertEquals(1, $shipment1->customer_shipment_sequence);
        $this->assertFalse($shipment1->is_delayed);
        $this->assertFalse($shipment1->is_damaged);
        $this->assertFalse($shipment1->has_complaint);

        // 2nd Shipment: verify repeat purchase sequence is automatically tracked!
        $response2 = $this->actingAs($this->client)->post(route('shipments.store'), [
            'shipment_type' => 'international',
            'service_type' => 'standard',
            'weight' => 1.5,
            'origin_zone_id' => $this->ktmZone->id,
            'destination_zone_id' => $this->pkrZone->id,
            'sender_name' => $this->client->name,
            'sender_phone' => $this->client->phone,
            'sender_address' => 'Thamel, Kathmandu',
            'pickup_address' => ['Thamel, Kathmandu'],
            'pickup_name' => [$this->client->name],
            'pickup_phone' => [$this->client->phone],
            'receiver_name' => 'Alice Cooper',
            'receiver_phone' => '+44-20-7946-0912',
            'receiver_street' => '221B Baker Street',
            'receiver_city' => 'London',
            'receiver_state' => 'Greater London',
            'receiver_postal_code' => 'NW1 6XE',
            'receiver_country' => 'United Kingdom',
            'schedule_doorstep_pickup' => '0',
            'acquisition_source' => 'direct_portal',
            'invoice_data_json' => json_encode($invoiceData),
            'packing_list_data_json' => json_encode($packingListData),
        ]);

        $response2->assertSessionHasNoErrors();
        $response2->assertRedirect();
        $shipment2 = Shipment::where('receiver_name', 'Alice Cooper')->first();
        $this->assertNotNull($shipment2);
        $this->assertTrue($shipment2->is_repeat_customer);
        $this->assertEquals(2, $shipment2->customer_shipment_sequence);
        $this->assertEquals('United Kingdom', $shipment2->destination_country);
    }

    public function test_delivery_status_calculates_transit_time_and_delay(): void
    {
        $shipment = Shipment::create([
            'hawb_number' => 'HAWB-TEST-001',
            'tracking_number' => 'NP-TEST-001',
            'customer_id' => $this->client->id,
            'sender_name' => 'Nepal Sender',
            'sender_phone' => '9800000000',
            'sender_address' => 'Thamel',
            'sender_city' => 'Kathmandu',
            'sender_country' => 'Nepal',
            'receiver_name' => 'Tokyo Consignee',
            'receiver_phone' => '+81-3-1234-5678',
            'receiver_address' => 'Shinjuku',
            'receiver_city' => 'Tokyo',
            'receiver_country' => 'Japan',
            'actual_weight' => 2.0,
            'chargeable_weight' => 2.0,
            'shipping_cost' => 3500,
            'total_amount' => 3500,
            'status' => 'in_transit',
        ]);

        $shipment->created_at = now()->subDays(5);
        $shipment->estimated_delivery = now()->subDays(1); // estimated 1 day ago -> delayed!
        $shipment->save();

        // Mark as delivered via model method
        $shipment->recordDeliveryTelemetry();
        $shipment->status = 'delivered';
        $shipment->save();

        $shipment->refresh();
        $this->assertEquals('delivered', $shipment->status);
        $this->assertNotNull($shipment->delivered_at);
        $this->assertGreaterThan(0, (float)$shipment->transit_time_hours);
        $this->assertGreaterThan(0, (float)$shipment->transit_time_days);
        $this->assertTrue($shipment->is_delayed);
        $this->assertGreaterThan(0, (float)$shipment->delay_hours);
    }

    public function test_client_can_report_issue_under_any_situation_and_updates_shipment_telemetry(): void
    {
        Storage::fake('public');

        $shipment = Shipment::create([
            'hawb_number' => 'HAWB-TEST-002',
            'tracking_number' => 'NP-ISSUE-100',
            'customer_id' => $this->client->id,
            'sender_name' => 'Nepal Sender',
            'sender_phone' => '9800000000',
            'sender_address' => 'Thamel',
            'sender_city' => 'Kathmandu',
            'sender_country' => 'Nepal',
            'receiver_name' => 'Damaged Goods Receiver',
            'receiver_phone' => '+1-555-9988',
            'receiver_address' => 'Broad Street',
            'receiver_city' => 'Philadelphia',
            'receiver_country' => 'United States',
            'actual_weight' => 3.0,
            'chargeable_weight' => 3.0,
            'shipping_cost' => 4500,
            'total_amount' => 4500,
            'status' => 'out_for_delivery',
        ]);

        $file = UploadedFile::fake()->image('damage_photo.jpg');

        $response = $this->actingAs($this->client)->post(route('shipments.issues.store', $shipment->tracking_number), [
            'issue_type' => 'damage',
            'situation_description' => 'The outer cardboard carton arrived crushed and water damaged, shattering 2 handcrafted ceramic singing bowls inside.',
            'contact_name' => 'Sarah Connor',
            'contact_email' => 'sarah@example.com',
            'contact_phone' => '+1-555-9988',
            'claimed_amount' => 85.00,
            'claimed_currency' => 'USD',
            'attachment' => $file,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify Issue recorded
        $issue = ShipmentIssue::where('shipment_id', $shipment->id)->first();
        $this->assertNotNull($issue);
        $this->assertEquals('damage', $issue->issue_type);
        $this->assertEquals(85.00, (float)$issue->claimed_amount);
        $this->assertEquals('USD', $issue->claimed_currency);
        $this->assertNotNull($issue->attachment_file);
        Storage::disk('public')->assertExists($issue->attachment_file);

        // Verify Shipment Telemetry flags updated
        $shipment->refresh();
        $this->assertTrue($shipment->has_complaint);
        $this->assertEquals(1, $shipment->complaint_count);
        $this->assertTrue($shipment->is_damaged);
        $this->assertStringContainsString('shattering 2 handcrafted ceramic', $shipment->damage_description);
        $this->assertNotNull($shipment->damage_reported_at);

        // Verify tracking timeline received the event
        $timeline = $shipment->tracking_timeline;
        $lastTimeline = end($timeline);
        $this->assertEquals('issue_reported', $lastTimeline['status']);
    }

    public function test_admin_analytics_dashboard_displays_18_metrics_and_issues(): void
    {
        // Seed a shipment with full metrics
        Shipment::create([
            'hawb_number' => 'HAWB-ANALYTICS-01',
            'tracking_number' => 'NP-ANALYTICS-01',
            'customer_id' => $this->client->id,
            'sender_name' => 'Nepal Sender',
            'sender_phone' => '9800000000',
            'sender_address' => 'Thamel',
            'sender_city' => 'Kathmandu',
            'sender_country' => 'Nepal',
            'receiver_name' => 'Berlin Consignee',
            'receiver_phone' => '+49-30-123456',
            'receiver_address' => 'Alexanderplatz',
            'receiver_city' => 'Berlin',
            'receiver_country' => 'Germany',
            'destination_country' => 'Germany',
            'actual_weight' => 5.0,
            'volumetric_weight' => 6.0,
            'chargeable_weight' => 6.0,
            'shipping_cost' => 8000,
            'total_amount' => 8000,
            'price_quoted' => 8000,
            'price_sold' => 8000,
            'cost_amount' => 5200,
            'gross_margin' => 2800,
            'gross_margin_percentage' => 35.0,
            'acquisition_source' => 'sales_representative',
            'vendor_name' => 'Lufthansa Cargo / Global Hub',
            'is_repeat_customer' => true,
            'status' => 'delivered',
            'transit_time_days' => 4.2,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.analytics'));

        $response->assertStatus(200);
        $response->assertSee('Operational Intelligence Pipeline');
        $response->assertSee('Total Price Sold');
        $response->assertSee('Gross Profit Margin');
        $response->assertSee('Delay Rate');
        $response->assertSee('Damage Rate');
        $response->assertSee('Repeat Purchase Rate');
        $response->assertSee('Acquisition Channels');
        $response->assertSee('Sales Executive');
        $response->assertSee('Germany');
    }
}
