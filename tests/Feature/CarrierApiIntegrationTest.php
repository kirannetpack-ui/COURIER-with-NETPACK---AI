<?php

namespace Tests\Feature;

use App\Models\Shipment;
use App\Models\User;
use App\Services\CarrierTrackingSyncService;
use App\Services\Tracking\CarrierTrackingGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CarrierApiIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private CarrierTrackingGateway $gateway;
    private CarrierTrackingSyncService $syncService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->gateway = app(CarrierTrackingGateway::class);
        $this->syncService = app(CarrierTrackingSyncService::class);
    }

    public function test_gateway_detects_carrier_formats_and_generates_direct_urls(): void
    {
        // DHL: 10 numeric digits
        $dhlWaybill = '1234567890';
        $this->assertEquals('dhl', $this->gateway->detectCarrier($dhlWaybill));
        $dhlUrl = $this->gateway->getCarrierUrl('dhl', $dhlWaybill);
        $this->assertStringContainsString('dhl.com', $dhlUrl);
        $this->assertStringContainsString($dhlWaybill, $dhlUrl);

        // FedEx: 12 numeric digits
        $fedexWaybill = '794612345678';
        $this->assertEquals('fedex', $this->gateway->detectCarrier($fedexWaybill));
        $fedexUrl = $this->gateway->getCarrierUrl('fedex', $fedexWaybill);
        $this->assertStringContainsString('fedex.com', $fedexUrl);
        $this->assertStringContainsString($fedexWaybill, $fedexUrl);

        // UPS: 1Z format
        $upsWaybill = '1Z9999999999999999';
        $this->assertEquals('ups', $this->gateway->detectCarrier($upsWaybill));
        $upsUrl = $this->gateway->getCarrierUrl('ups', $upsWaybill);
        $this->assertStringContainsString('ups.com', $upsUrl);
        $this->assertStringContainsString($upsWaybill, $upsUrl);

        // Aramex
        $aramexUrl = $this->gateway->getCarrierUrl('aramex', '3099887766');
        $this->assertStringContainsString('aramex.com', $aramexUrl);
    }

    public function test_carrier_sync_service_advances_milestones_and_updates_shipment(): void
    {
        $client = User::factory()->create(['user_type' => User::TYPE_CLIENT]);

        $shipment = Shipment::create([
            'hawb_number' => 'HAWB-CARRIER-01',
            'tracking_number' => 'NP-CARRIER-01',
            'customer_id' => $client->id,
            'shipment_type' => 'international',
            'service_type' => 'express',
            'sender_name' => 'Nepal Exporter',
            'sender_phone' => '9800000000',
            'sender_address' => 'Thamel',
            'sender_city' => 'Kathmandu',
            'receiver_name' => 'John Doe',
            'receiver_phone' => '+1-555-1234',
            'receiver_address' => '5th Ave',
            'receiver_city' => 'New York',
            'receiver_country' => 'United States',
            'actual_weight' => 2.5,
            'chargeable_weight' => 2.5,
            'shipping_cost' => 5000,
            'total_amount' => 5000,
            'status' => 'in_transit',
            'agency_milestone' => 'last_mile_handover',
            'last_mile_carrier_name' => 'DHL',
            'last_mile_tracking_number' => '9876543210',
        ]);

        $res = $this->syncService->syncShipment($shipment);

        $this->assertTrue($res['success']);
        $this->assertTrue($res['updated']);

        $shipment->refresh();
        $this->assertNotEmpty($shipment->tracking_history);
        $this->assertNotNull($shipment->carrier_tracking_url);
        $this->assertStringContainsString('dhl.com', $shipment->carrier_tracking_url);
    }

    public function test_inbound_carrier_webhook_updates_shipment_telemetry(): void
    {
        $client = User::factory()->create(['user_type' => User::TYPE_CLIENT]);

        $shipment = Shipment::create([
            'hawb_number' => 'HAWB-WEBHOOK-01',
            'tracking_number' => 'NP-WEBHOOK-01',
            'customer_id' => $client->id,
            'shipment_type' => 'international',
            'service_type' => 'express',
            'sender_name' => 'Kathmandu Trader',
            'sender_phone' => '9800000000',
            'sender_address' => 'Patan',
            'sender_city' => 'Lalitpur',
            'receiver_name' => 'Sarah Connor',
            'receiver_phone' => '+1-555-9988',
            'receiver_address' => 'Sunset Blvd',
            'receiver_city' => 'Los Angeles',
            'receiver_country' => 'United States',
            'actual_weight' => 3.0,
            'chargeable_weight' => 3.0,
            'shipping_cost' => 6000,
            'total_amount' => 6000,
            'status' => 'in_transit',
            'last_mile_carrier_name' => 'FedEx',
            'last_mile_tracking_number' => '794699887766',
        ]);

        $payload = [
            'tracking_number' => '794699887766',
            'status' => 'OUT_FOR_DELIVERY',
            'location' => 'Los Angeles Delivery Station, CA',
            'description' => 'On delivery vehicle with courier',
            'timestamp' => now()->toIso8601String(),
        ];

        $response = $this->postJson('/api/webhooks/carrier-tracking/fedex', $payload);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $shipment->refresh();
        $this->assertEquals('out_for_delivery', $shipment->status);

        $history = $shipment->tracking_history;
        $latest = end($history);
        $this->assertEquals('out_for_delivery', $latest['event_code']);
        $this->assertStringContainsString('Los Angeles', $latest['location']);
    }
}
