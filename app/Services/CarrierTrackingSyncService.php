<?php

namespace App\Services;

use App\Models\LastMileCarrier;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

use App\Services\Tracking\CarrierTrackingGateway;

class CarrierTrackingSyncService
{
    private CarrierTrackingGateway $gateway;

    public function __construct(
        private readonly ShipmentScanService $scanService,
        private readonly AutomatedTrackingService $automatedTracking,
        ?CarrierTrackingGateway $gateway = null
    ) {
        $this->gateway = $gateway ?: new CarrierTrackingGateway();
    }

    /**
     * Synchronize a single international shipment with its global last-mile carrier
     */
    public function syncShipment(Shipment $shipment, ?User $actor = null): array
    {
        if (empty($shipment->last_mile_tracking_number)) {
            return [
                'success' => false,
                'message' => 'Shipment has no last-mile carrier tracking number assigned.',
            ];
        }

        $carrierName = strtolower(trim($shipment->last_mile_carrier_name ?: ($shipment->lastMileCarrier?->name ?: 'carrier')));
        $trackingCode = trim($shipment->last_mile_tracking_number);

        // Fetch or simulate carrier telemetry
        $telemetry = $this->queryCarrierTracking($carrierName, $trackingCode, $shipment);

        if (!$telemetry['success']) {
            return $telemetry;
        }

        $newMilestone = $telemetry['milestone'] ?? ($telemetry['event_code'] ?? 'in_transit');
        $eventCode = $telemetry['event_code'] ?? ($telemetry['milestone'] ?? 'in_transit');
        $location = $telemetry['location'] ?? ($shipment->receiver_city ?: 'Destination Distribution Hub');
        $description = $telemetry['description'] ?? 'Carrier checkpoint verified';
        $timestamp = $telemetry['timestamp'] ?? now()->toIso8601String();

        // If the latest event in tracking history already matches this carrier event, skip duplicate
        $history = $shipment->tracking_history ?? [];
        $latest = end($history);
        if ($latest && ($latest['event_code'] ?? '') === $eventCode && ($latest['location'] ?? '') === $location) {
            return [
                'success' => true,
                'updated' => false,
                'message' => 'Shipment is already up to date with latest carrier checkpoint.',
                'telemetry' => $telemetry,
            ];
        }

        $actor = $this->automatedTracking->getSystemActor($actor);

        DB::transaction(function () use ($shipment, $eventCode, $location, $description, $actor, $timestamp, $carrierName, $trackingCode) {
            $this->scanService->record(
                $shipment,
                $eventCode,
                $location,
                $description,
                $actor,
                'carrier_api_sync',
                $timestamp,
                [
                    'carrier_name' => ucfirst($carrierName),
                    'carrier_tracking_number' => $trackingCode,
                    'carrier_sync_at' => now()->toIso8601String(),
                ]
            );

            $updates = [
                'current_location' => $location,
                'agency_milestone' => $eventCode,
            ];

            if ($eventCode === 'delivered') {
                $updates['delivered_at'] = Carbon::parse($timestamp);
                $updates['status'] = 'delivered';
            }

            $shipment->update($updates);
        });

        $this->automatedTracking->notifySubscribers(
            $shipment->tracking_number,
            $telemetry['status_label'],
            $description,
            $location
        );

        return [
            'success' => true,
            'updated' => true,
            'message' => "Successfully synced with {$carrierName}: {$telemetry['status_label']}",
            'telemetry' => $telemetry,
        ];
    }

    /**
     * Batch sync all active international shipments that have a last-mile carrier waybill
     */
    public function syncAllActiveShipments(): array
    {
        $shipments = Shipment::where('shipment_type', '!=', 'domestic')
            ->whereNotNull('last_mile_tracking_number')
            ->whereNotIn('status', ['delivered', 'cancelled', 'returned'])
            ->get();

        $synced = 0;
        $updated = 0;
        $errors = 0;

        foreach ($shipments as $shipment) {
            $res = $this->syncShipment($shipment);
            $synced++;
            if (!empty($res['updated'])) {
                $updated++;
            }
            if (empty($res['success'])) {
                $errors++;
            }
        }

        return [
            'total_checked' => $synced,
            'updated_count' => $updated,
            'error_count' => $errors,
        ];
    }

    /**
     * Inbound carrier webhook receiver
     */
    public function handleInboundWebhook(string $carrier, array $payload): array
    {
        $trackingNumber = $payload['tracking_number']
            ?? ($payload['waybill']
            ?? ($payload['data']['tracking_number']
            ?? ($payload['event']['tracking_number'] ?? null)));

        if (!$trackingNumber) {
            return [
                'success' => false,
                'message' => 'No tracking number found in carrier webhook payload.',
            ];
        }

        $shipment = Shipment::where('last_mile_tracking_number', $trackingNumber)
            ->orWhere('tracking_number', $trackingNumber)
            ->orWhere('hawb_number', $trackingNumber)
            ->first();

        if (!$shipment) {
            return [
                'success' => false,
                'message' => "No shipment found matching carrier tracking number {$trackingNumber}.",
            ];
        }

        $statusCode = strtoupper($payload['status'] ?? ($payload['event_code'] ?? ($payload['data']['status'] ?? 'IN_TRANSIT')));
        $location = $payload['location'] ?? ($payload['city'] ?? ($payload['data']['location'] ?? 'Destination Distribution Hub'));
        $description = $payload['description'] ?? ($payload['message'] ?? 'Carrier checkpoint scan');
        $eventTime = $payload['timestamp'] ?? now()->toIso8601String();

        $mapping = $this->mapCarrierStatusToNetpackMilestone($statusCode);

        $actor = $this->automatedTracking->getSystemActor();

        $this->scanService->record(
            $shipment,
            $mapping['event_code'],
            $location,
            "{$carrier} update: {$description}",
            $actor,
            'carrier_webhook',
            $eventTime,
            [
                'carrier' => $carrier,
                'carrier_code' => $statusCode,
                'raw_payload' => $payload,
            ]
        );

        $this->automatedTracking->notifySubscribers($shipment->tracking_number, $mapping['status_label'], $description, $location);

        return [
            'success' => true,
            'shipment_id' => $shipment->id,
            'tracking_number' => $shipment->tracking_number,
            'milestone' => $mapping['event_code'],
        ];
    }

    /**
     * Query tracking information via CarrierTrackingGateway (17TRACK, DirectCarrier, or SmartAutonomous)
     */
    protected function queryCarrierTracking(string $carrier, string $trackingCode, Shipment $shipment): array
    {
        return $this->gateway->track($trackingCode, [
            'carrier_name' => $carrier,
            'shipment' => $shipment,
            'destination_city' => $shipment->receiver_city,
            'destination_country' => $shipment->receiver_country,
        ]);
    }

    /**
     * Standard carrier status code mapper
     */
    protected function mapCarrierStatusToNetpackMilestone(string $code): array
    {
        $code = strtoupper(trim($code));

        if (in_array($code, ['DL', 'DELIVERED', 'POD_CONFIRMED', 'SUCCESS'])) {
            return ['event_code' => 'delivered', 'status_label' => 'Delivered'];
        }

        if (in_array($code, ['OD', 'OUT_FOR_DELIVERY', 'WITH_COURIER', 'DISPATCHED'])) {
            return ['event_code' => 'out_for_delivery', 'status_label' => 'Out for Delivery'];
        }

        if (in_array($code, ['EX', 'EXCEPTION', 'ATTEMPTED', 'FAILED_ATTEMPT', 'UNAVAILABLE'])) {
            return ['event_code' => 'delivery_attempted', 'status_label' => 'Delivery Attempted'];
        }

        if (in_array($code, ['CC', 'CUSTOMS', 'CLEARANCE_DELAY', 'DUTY_PAYMENT'])) {
            return ['event_code' => 'customs_hold', 'status_label' => 'Customs Review'];
        }

        if (in_array($code, ['AR', 'ARRIVED', 'SORT_FACILITY', 'DEPOT_ARRIVAL'])) {
            return ['event_code' => 'destination_facility_arrival', 'status_label' => 'Arrived at Destination Facility'];
        }

        return ['event_code' => 'transit_facility_arrival', 'status_label' => 'In Transit'];
    }
}
