<?php

namespace Tests\Feature;

use App\Models\DeliveryZone;
use App\Models\HsCode;
use App\Models\Shipment;
use App\Models\User;
use Database\Seeders\HsCodeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ShipmentDocumentAndBoxAllocationTest extends TestCase
{
    use RefreshDatabase;

    protected User $client;
    protected User $otherClient;
    protected DeliveryZone $ktmZone;
    protected DeliveryZone $pkrZone;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(HsCodeSeeder::class);

        $this->client = User::factory()->create([
            'user_type' => User::TYPE_CLIENT,
            'name' => 'Himalayan Exporters Pvt Ltd',
            'email' => 'himalayan@example.com',
            'phone' => '9851022334',
            'pan_number' => '600123456',
        ]);

        $this->otherClient = User::factory()->create([
            'user_type' => User::TYPE_CLIENT,
            'name' => 'Other Merchant',
            'email' => 'other@example.com',
        ]);

        $this->ktmZone = DeliveryZone::create([
            'zone_code' => 'KTM-METRO',
            'zone_name' => 'Kathmandu Metro Zone',
            'district' => 'Kathmandu',
            'is_active' => true,
            'approval_status' => 'approved',
        ]);

        $this->pkrZone = DeliveryZone::create([
            'zone_code' => 'PKR-LAKE',
            'zone_name' => 'Pokhara Lake Zone',
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

    public function test_shipment_create_page_contains_invoicing_wco_and_packing_matrix(): void
    {
        $response = $this->actingAs($this->client)->get(route('shipments.create'));

        $response->assertStatus(200);
        $response->assertSee('Commercial Invoice Preparation');
        $response->assertSee('WCO Harmonized System (HS) Tariffs Explorer', false);
        $response->assertSee('Package Weight & Smart Packing List Matrix', false);
        $response->assertSee('Nepal Tax & Customs Documentation', false);
        $response->assertSee('seller_bill_file');
        $response->assertSee('invoice_data_json');
        $response->assertSee('packing_list_data_json');
    }

    public function test_can_create_international_shipment_with_commercial_invoice_and_single_box_auto_allocation(): void
    {
        $invoiceData = [
            'invoice_number' => 'INV-2026-9001',
            'invoice_date' => '2026-09-20',
            'currency' => 'USD',
            'incoterm' => 'DAP',
            'export_reason' => 'Commercial Sale / Export',
            'shipper_pan_vat' => '600123456',
            'shipper_exim_code' => 'NP6001234560',
            'consignee_tax_id' => 'US-EIN-992810',
            'subtotal' => 280.00,
            'items' => [
                [
                    'description' => 'Handmade Pashmina Shawls',
                    'hs_code' => '6214.20.00',
                    'origin_country' => 'Nepal',
                    'quantity' => 4,
                    'uom' => 'PCS',
                    'unit_value' => 45.00,
                    'total_value' => 180.00,
                ],
                [
                    'description' => 'Tibetan Meditation Singing Bowl Set',
                    'hs_code' => '9206.00.00',
                    'origin_country' => 'Nepal',
                    'quantity' => 2,
                    'uom' => 'SET',
                    'unit_value' => 50.00,
                    'total_value' => 100.00,
                ],
            ],
        ];

        $packingListData = [
            'total_boxes' => 1,
            'total_gross_weight' => 3.5,
            'total_volumetric_weight' => 2.8,
            'boxes' => [
                [
                    'box_number' => 1,
                    'length' => 40,
                    'width' => 35,
                    'height' => 20,
                    'gross_weight' => 3.5,
                    'volumetric_weight' => 2.8,
                    'items' => [
                        [
                            'item_name' => 'Handmade Pashmina Shawls',
                            'hs_code' => '6214.20.00',
                            'quantity' => 4,
                            'uom' => 'PCS',
                        ],
                        [
                            'item_name' => 'Tibetan Meditation Singing Bowl Set',
                            'hs_code' => '9206.00.00',
                            'quantity' => 2,
                            'uom' => 'SET',
                        ],
                    ],
                ],
            ],
        ];

        $payload = [
            'shipment_type' => 'international',
            'service_type' => 'express',
            'weight' => 3.5,
            'length' => 40,
            'width' => 35,
            'height' => 20,
            'description' => 'Pashmina Shawls and Singing Bowls',
            'schedule_doorstep_pickup' => '0',
            'sender_name' => $this->client->name,
            'sender_phone' => $this->client->phone,
            'sender_address' => 'Thamel, Kathmandu',
            'receiver_name' => 'Zen Imports LLC',
            'receiver_street' => '550 Broadway St, Suite 400',
            'receiver_city' => 'New York',
            'receiver_state' => 'NY',
            'receiver_postal_code' => '10012',
            'receiver_country' => 'United States',
            'receiver_tax_id' => 'US-EIN-992810',
            'origin_zone_id' => $this->ktmZone->id,
            'destination_zone_id' => $this->pkrZone->id,
            'invoice_data_json' => json_encode($invoiceData),
            'packing_list_data_json' => json_encode($packingListData),
        ];

        $response = $this->actingAs($this->client)->post(route('shipments.store'), $payload);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('shipments', [
            'customer_id' => $this->client->id,
            'shipment_type' => 'international',
            'receiver_country' => 'United States',
        ]);

        $shipment = Shipment::where('customer_id', $this->client->id)->latest()->first();
        $this->assertNotNull($shipment);
        $this->assertEquals('INV-2026-9001', $shipment->invoice_data['invoice_number']);
        $this->assertEquals(280.00, $shipment->invoice_data['subtotal']);
        $this->assertCount(2, $shipment->invoice_data['items']);
        $this->assertEquals('6214.20.00', $shipment->invoice_data['items'][0]['hs_code']);

        $this->assertEquals(1, $shipment->packing_list_data['total_boxes']);
        $this->assertCount(1, $shipment->boxes);
        $this->assertCount(2, $shipment->boxes[0]['items']);
    }

    public function test_can_create_multi_box_shipment_with_box_matrix_allocation(): void
    {
        $invoiceData = [
            'invoice_number' => 'INV-2026-9002',
            'invoice_date' => '2026-09-20',
            'currency' => 'USD',
            'incoterm' => 'DAP',
            'export_reason' => 'Commercial Sale / Export',
            'subtotal' => 600.00,
            'items' => [
                [
                    'description' => 'Himalayan Herbal Tea Packets',
                    'hs_code' => '0902.10.00',
                    'quantity' => 10,
                    'uom' => 'PKT',
                    'unit_value' => 20.00,
                    'total_value' => 200.00,
                ],
                [
                    'description' => 'Nepalese Handmade Lokta Journals',
                    'hs_code' => '4820.10.00',
                    'quantity' => 20,
                    'uom' => 'PCS',
                    'unit_value' => 20.00,
                    'total_value' => 400.00,
                ],
            ],
        ];

        // Multi-box: Box 1 has 10 teas; Box 2 has 20 journals
        $packingListData = [
            'total_boxes' => 2,
            'total_gross_weight' => 8.0,
            'total_volumetric_weight' => 9.2,
            'boxes' => [
                [
                    'box_number' => 1,
                    'length' => 35,
                    'width' => 30,
                    'height' => 25,
                    'gross_weight' => 3.0,
                    'volumetric_weight' => 5.25,
                    'items' => [
                        [
                            'item_name' => 'Himalayan Herbal Tea Packets',
                            'hs_code' => '0902.10.00',
                            'quantity' => 10,
                            'uom' => 'PKT',
                        ],
                    ],
                ],
                [
                    'box_number' => 2,
                    'length' => 40,
                    'width' => 30,
                    'height' => 20,
                    'gross_weight' => 5.0,
                    'volumetric_weight' => 4.8,
                    'items' => [
                        [
                            'item_name' => 'Nepalese Handmade Lokta Journals',
                            'hs_code' => '4820.10.00',
                            'quantity' => 20,
                            'uom' => 'PCS',
                        ],
                    ],
                ],
            ],
        ];

        $payload = [
            'shipment_type' => 'international',
            'service_type' => 'economy',
            'weight' => 8.0,
            'description' => 'Tea and Paper Consignment',
            'schedule_doorstep_pickup' => '0',
            'sender_name' => $this->client->name,
            'sender_phone' => $this->client->phone,
            'sender_address' => 'Kathmandu',
            'receiver_name' => 'London Tea Merchant',
            'receiver_street' => '24 Oxford Street',
            'receiver_city' => 'London',
            'receiver_state' => 'Greater London',
            'receiver_postal_code' => 'W1D 1BS',
            'receiver_country' => 'United Kingdom',
            'origin_zone_id' => $this->ktmZone->id,
            'destination_zone_id' => $this->pkrZone->id,
            'invoice_data_json' => json_encode($invoiceData),
            'packing_list_data_json' => json_encode($packingListData),
        ];

        $response = $this->actingAs($this->client)->post(route('shipments.store'), $payload);

        $response->assertSessionHasNoErrors();
        $shipment = Shipment::where('customer_id', $this->client->id)->latest()->first();

        $this->assertNotNull($shipment);
        $this->assertEquals(2, $shipment->packing_list_data['total_boxes']);
        $this->assertCount(2, $shipment->boxes);
        $this->assertEquals(10, $shipment->boxes[0]['items'][0]['quantity']);
        $this->assertEquals(20, $shipment->boxes[1]['items'][0]['quantity']);
        $this->assertEquals(8.0, $shipment->actual_weight);
    }

    public function test_can_upload_seller_tax_bill_compliant_with_nepal_govt_rules(): void
    {
        Storage::fake('public');

        $fakeBill = UploadedFile::fake()->create('ird_tax_invoice_081_82.pdf', 500, 'application/pdf');

        $payload = [
            'shipment_type' => 'domestic',
            'service_type' => 'standard',
            'weight' => 2.0,
            'description' => 'Textile Fabric with VAT Bill',
            'schedule_doorstep_pickup' => '0',
            'sender_name' => $this->client->name,
            'sender_phone' => $this->client->phone,
            'sender_address' => 'Kathmandu Gateway',
            'delivery_name' => ['Pokhara Storehouse'],
            'delivery_phone' => ['9841000000'],
            'delivery_address' => ['Lakeside, Ward 6, Pokhara'],
            'delivery_district' => ['Kaski'],
            'delivery_province' => ['Gandaki'],
            'origin_zone_id' => $this->ktmZone->id,
            'destination_zone_id' => $this->pkrZone->id,
            'seller_bill_type' => 'vat_invoice',
            'seller_bill_number' => 'VAT-81/82-00452',
            'seller_bill_file' => $fakeBill,
        ];

        $response = $this->actingAs($this->client)->post(route('shipments.store'), $payload);

        $response->assertSessionHasNoErrors();
        $shipment = Shipment::where('customer_id', $this->client->id)->latest()->first();

        $this->assertNotNull($shipment);
        $this->assertNotNull($shipment->seller_bill_file);
        Storage::disk('public')->assertExists($shipment->seller_bill_file);

        // Can view/download attached seller bill
        $billResponse = $this->actingAs($this->client)->get(route('shipments.seller-bill', $shipment->id));
        $billResponse->assertStatus(200);
    }

    public function test_can_view_printable_commercial_invoice_document(): void
    {
        $shipment = Shipment::create([
            'tracking_number' => 'NP-EXP-2026-112233',
            'hawb_number' => 'HAWB-USA-998811',
            'customer_id' => $this->client->id,
            'shipment_type' => 'international',
            'service_type' => 'express',
            'actual_weight' => 2.5,
            'chargeable_weight' => 2.5,
            'sender_name' => $this->client->name,
            'sender_phone' => $this->client->phone,
            'sender_address' => 'Baluwatar, Kathmandu',
            'sender_city' => 'Kathmandu',
            'sender_country' => 'Nepal',
            'receiver_name' => 'Zen Imports Inc',
            'receiver_phone' => '+1-212-555-0199',
            'receiver_address' => "Zen Imports Inc\n550 Broadway\nNew York, NY\n10012\nUnited States",
            'receiver_city' => 'New York',
            'receiver_state' => 'NY',
            'receiver_country' => 'United States',
            'receiver_tax_id' => 'US-EIN-992810',
            'shipping_cost' => 4500,
            'total_amount' => 4500,
            'invoice_data' => [
                'invoice_number' => 'INV-2026-7788',
                'invoice_date' => '2026-09-20',
                'currency' => 'USD',
                'incoterm' => 'DAP',
                'export_reason' => 'Commercial Sale / Export',
                'shipper_pan_vat' => '600123456',
                'shipper_exim_code' => 'NP6001234560',
                'consignee_tax_id' => 'US-EIN-992810',
                'subtotal' => 350.00,
                'items' => [
                    [
                        'description' => 'Handmade Pashmina Shawls',
                        'hs_code' => '6214.20.00',
                        'origin_country' => 'Nepal',
                        'quantity' => 7,
                        'uom' => 'PCS',
                        'unit_value' => 50.00,
                        'total_value' => 350.00,
                    ],
                ],
            ],
        ]);

        $response = $this->actingAs($this->client)->get(route('shipments.invoice', $shipment->id));

        $response->assertStatus(200);
        $response->assertSee('COMMERCIAL INVOICE');
        $response->assertSee('INV-2026-7788');
        $response->assertSee('HAWB-USA-998811');
        $response->assertSee('600123456');
        $response->assertSee('NP6001234560');
        $response->assertSee('6214.20.00');
        $response->assertSee('Handmade Pashmina Shawls');
        $response->assertSee('USD 350.00');
        $response->assertSee('EXPORTER LEGAL DECLARATION');
    }

    public function test_can_view_printable_packing_list_document(): void
    {
        $shipment = Shipment::create([
            'tracking_number' => 'NP-EXP-2026-445566',
            'hawb_number' => 'HAWB-GBR-778899',
            'customer_id' => $this->client->id,
            'shipment_type' => 'international',
            'service_type' => 'economy',
            'actual_weight' => 12.0,
            'chargeable_weight' => 12.0,
            'sender_name' => $this->client->name,
            'sender_phone' => $this->client->phone,
            'sender_address' => 'Baluwatar, Kathmandu',
            'sender_city' => 'Kathmandu',
            'sender_country' => 'Nepal',
            'receiver_name' => 'Royal Tea Co',
            'receiver_phone' => '+44-20-7946-0912',
            'receiver_address' => "Royal Tea Co\nPiccadilly\nLondon\nW1J 9HP\nUnited Kingdom",
            'receiver_city' => 'London',
            'receiver_country' => 'United Kingdom',
            'shipping_cost' => 9500,
            'total_amount' => 9500,
            'packing_list_data' => [
                'total_boxes' => 2,
                'total_gross_weight' => 12.0,
                'total_volumetric_weight' => 11.5,
                'boxes' => [
                    [
                        'box_number' => 1,
                        'length' => 45,
                        'width' => 35,
                        'height' => 25,
                        'gross_weight' => 6.0,
                        'volumetric_weight' => 7.88,
                        'items' => [
                            [
                                'item_name' => 'Orthodox Golden Tips Black Tea',
                                'hs_code' => '0902.30.00',
                                'quantity' => 15,
                                'uom' => 'PKT',
                            ],
                        ],
                    ],
                    [
                        'box_number' => 2,
                        'length' => 40,
                        'width' => 30,
                        'height' => 20,
                        'gross_weight' => 6.0,
                        'volumetric_weight' => 4.8,
                        'items' => [
                            [
                                'item_name' => 'Green Himalayan Pearl Tea',
                                'hs_code' => '0902.10.00',
                                'quantity' => 15,
                                'uom' => 'PKT',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $response = $this->actingAs($this->client)->get(route('shipments.packing-list', $shipment->id));

        $response->assertStatus(200);
        $response->assertSee('PACKING LIST');
        $response->assertSee('2 Boxes');
        $response->assertSee('Package #1 of 2');
        $response->assertSee('Package #2 of 2');
        $response->assertSee('Orthodox Golden Tips Black Tea');
        $response->assertSee('Green Himalayan Pearl Tea');
        $response->assertSee('12.00 KG');
    }

    public function test_unauthorized_user_cannot_access_shipping_documents(): void
    {
        $shipment = Shipment::create([
            'tracking_number' => 'NP-DOM-2026-990011',
            'customer_id' => $this->client->id,
            'shipment_type' => 'domestic',
            'service_type' => 'standard',
            'actual_weight' => 1.0,
            'chargeable_weight' => 1.0,
            'sender_name' => $this->client->name,
            'sender_phone' => $this->client->phone,
            'sender_address' => 'Kathmandu',
            'receiver_name' => 'Receiver',
            'receiver_address' => 'Pokhara',
            'receiver_city' => 'Pokhara',
            'receiver_country' => 'Nepal',
            'shipping_cost' => 300,
            'total_amount' => 300,
        ]);

        // Attempt as another unprivileged client
        $response = $this->actingAs($this->otherClient)->get(route('shipments.invoice', $shipment->id));
        $response->assertStatus(403);

        $responsePack = $this->actingAs($this->otherClient)->get(route('shipments.packing-list', $shipment->id));
        $responsePack->assertStatus(403);
    }

    public function test_cannot_create_shipment_when_packing_list_quantity_exceeds_invoice_declared_quantity(): void
    {
        $invoiceData = [
            'invoice_number' => 'INV-2026-EXCEED',
            'invoice_date' => '2026-09-20',
            'currency' => 'USD',
            'incoterm' => 'DAP',
            'subtotal' => 200.00,
            'items' => [
                [
                    'description' => 'Himalayan Herbal Tea Packets',
                    'hs_code' => '0902.10.00',
                    'quantity' => 10,
                    'uom' => 'PKT',
                    'unit_value' => 20.00,
                    'total_value' => 200.00,
                ],
            ],
        ];

        // Box 1 has 6 packets, Box 2 has 8 packets (Total = 14 > 10 declared in invoice)
        $invalidPackingListData = [
            'total_boxes' => 2,
            'total_gross_weight' => 5.0,
            'total_volumetric_weight' => 6.0,
            'boxes' => [
                [
                    'box_number' => 1,
                    'length' => 30,
                    'width' => 25,
                    'height' => 20,
                    'gross_weight' => 2.5,
                    'volumetric_weight' => 3.0,
                    'items' => [
                        [
                            'item_index' => 0,
                            'item_name' => 'Himalayan Herbal Tea Packets',
                            'hs_code' => '0902.10.00',
                            'quantity' => 6,
                            'uom' => 'PKT',
                        ],
                    ],
                ],
                [
                    'box_number' => 2,
                    'length' => 30,
                    'width' => 25,
                    'height' => 20,
                    'gross_weight' => 2.5,
                    'volumetric_weight' => 3.0,
                    'items' => [
                        [
                            'item_index' => 0,
                            'item_name' => 'Himalayan Herbal Tea Packets',
                            'hs_code' => '0902.10.00',
                            'quantity' => 8,
                            'uom' => 'PKT',
                        ],
                    ],
                ],
            ],
        ];

        $payload = [
            'shipment_type' => 'international',
            'service_type' => 'express',
            'package_type' => 'parcel',
            'weight' => 5.0,
            'receiver_name' => 'London Wholesale Ltd',
            'receiver_street' => '100 Regent Street',
            'receiver_city' => 'London',
            'receiver_state' => 'Greater London',
            'receiver_postal_code' => 'W1B 5TH',
            'receiver_country' => 'United Kingdom',
            'receiver_phone' => '+44-20-7946-0919',
            'schedule_doorstep_pickup' => '0',
            'sender_name' => $this->client->name,
            'sender_phone' => $this->client->phone,
            'sender_address' => 'Kathmandu Gateway',
            'invoice_data_json' => json_encode($invoiceData),
            'packing_list_data_json' => json_encode($invalidPackingListData),
        ];

        $response = $this->actingAs($this->client)->post(route('shipments.store'), $payload);

        // Must reject with validation error
        $response->assertSessionHasErrors(['packing_list_data_json']);
        $this->assertDatabaseMissing('shipments', [
            'customer_id' => $this->client->id,
            'receiver_city' => 'London',
        ]);
    }
}
