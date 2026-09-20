<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DomesticShipment;
use App\Models\Order;
use App\Models\PickupRequest;
use App\Models\Shipment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicTrackingApiController extends Controller
{
    /**
     * Privacy-safe JSON tracking endpoint for corporate B2B clients, APIs, and mobile apps
     */
    public function track(string $trackingNumber): JsonResponse
    {
        $normalized = strtoupper(trim(preg_replace('/[\s]+/', '', $trackingNumber)));

        // 1. Shipment search (by tracking_number, hawb_number, or last_mile_tracking_number)
        $shipment = Shipment::where('tracking_number', $normalized)
            ->orWhere('hawb_number', $normalized)
            ->orWhere('last_mile_tracking_number', $normalized)
            ->with(['hub', 'currentAgency', 'lastMileCarrier', 'mawb'])
            ->first();

        if ($shipment) {
            $statusInfo = $shipment->tracking_status;
            $serviceConfig = config('tracking.services.' . $shipment->service_type, config('tracking.services.default'));

            return response()->json([
                'success' => true,
                'data' => [
                    'tracking_number' => $shipment->formatted_tracking_number,
                    'hawb_number' => $shipment->hawb_number,
                    'mawb_number' => $shipment->mawb_number ?: ($shipment->mawb?->mawb_number),
                    'shipment_type' => $shipment->shipment_type,
                    'service_type' => $shipment->service_type,
                    'service_label' => $serviceConfig['label'] ?? 'Standard Cargo',
                    'status' => $shipment->status,
                    'status_label' => $statusInfo['label'] ?? ucfirst($shipment->status),
                    'status_description' => $statusInfo['description'] ?? '',
                    'origin' => [
                        'city' => $shipment->sender_city ?: 'Kathmandu',
                        'country' => $shipment->sender_country ?: 'Nepal',
                        'gateway' => 'TIA International Cargo Terminal (KTM)',
                    ],
                    'destination' => [
                        'city' => $shipment->receiver_city,
                        'country' => $shipment->receiver_country,
                        'state' => $shipment->receiver_state,
                    ],
                    'logistics' => [
                        'chargeable_weight_kg' => (float) ($shipment->chargeable_weight ?: $shipment->actual_weight),
                        'pieces' => is_array($shipment->boxes) ? count($shipment->boxes) : 1,
                        'estimated_delivery' => $shipment->estimated_delivery?->toIso8601String(),
                        'customs_mode' => $shipment->customs_mode ?? 'DDP',
                        'hub_code' => $shipment->hub?->hub_code,
                        'agency_name' => $shipment->currentAgency?->name,
                        'last_mile' => [
                            'carrier' => $shipment->last_mile_carrier_name ?: $shipment->lastMileCarrier?->name,
                            'waybill' => $shipment->last_mile_tracking_number,
                            'tracking_url' => $shipment->carrier_tracking_url ?: ($shipment->lastMileCarrier?->getTrackingUrl($shipment->last_mile_tracking_number)),
                        ],
                    ],
                    'events' => array_reverse($shipment->tracking_history ?: []),
                    'last_updated' => $shipment->updated_at->toIso8601String(),
                ],
            ]);
        }

        // 2. Domestic shipment search
        $dom = DomesticShipment::where('tracking_number', $normalized)
            ->with('domesticRate')
            ->first();

        if ($dom) {
            return response()->json([
                'success' => true,
                'data' => [
                    'tracking_number' => $dom->tracking_number,
                    'shipment_type' => 'domestic',
                    'service_type' => $dom->service_type,
                    'service_name' => $dom->service_name ?: 'Domestic Express',
                    'status' => $dom->status,
                    'status_label' => DomesticShipment::STATUS_LABELS[$dom->status] ?? ucfirst($dom->status),
                    'origin' => [
                        'city' => $dom->sender_city ?: 'Kathmandu',
                        'zone' => $dom->sender_zone,
                    ],
                    'destination' => [
                        'city' => $dom->receiver_city,
                        'zone' => $dom->receiver_zone,
                        'ward' => $dom->receiver_ward,
                    ],
                    'weight_kg' => (float) $dom->weight,
                    'estimated_delivery' => $dom->estimated_delivery_at?->toIso8601String(),
                    'events' => array_reverse($dom->tracking_history ?: []),
                    'last_updated' => $dom->updated_at->toIso8601String(),
                ],
            ]);
        }

        // 3. Pickup Request search
        $pickup = PickupRequest::where('tracking_number', $normalized)
            ->orWhere('order_reference', $normalized)
            ->first();

        if ($pickup) {
            return response()->json([
                'success' => true,
                'data' => [
                    'tracking_number' => $pickup->tracking_number ?: "PICKUP-{$pickup->id}",
                    'shipment_type' => 'pickup',
                    'service_tier' => $pickup->service_tier,
                    'status' => $pickup->status,
                    'pickup_window' => $pickup->scheduled_pickup_time?->toIso8601String(),
                    'origin' => [
                        'city' => $pickup->pickup_city ?: 'Kathmandu',
                        'district' => $pickup->pickup_district,
                    ],
                    'destination' => [
                        'city' => $pickup->delivery_city,
                        'district' => $pickup->delivery_district,
                    ],
                    'events' => array_reverse($pickup->status_history ?: []),
                    'last_updated' => $pickup->updated_at->toIso8601String(),
                ],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Consignment not found for the provided reference number.',
        ], 404);
    }
}
