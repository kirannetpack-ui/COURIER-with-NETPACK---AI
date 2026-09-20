<?php

namespace App\Services\Tracking\Drivers;

use App\Services\Tracking\Contracts\CarrierTrackingDriverInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SeventeenTrackDriver implements CarrierTrackingDriverInterface
{
    private const BASE_URL = 'https://api.17track.net/track/v2.2';

    public function __construct(private readonly ?string $apiKey = null)
    {
    }

    public function getName(): string
    {
        return '17track';
    }

    public function supports(string $carrier, string $trackingNumber): bool
    {
        // 17TRACK supports 2,200+ couriers globally
        return !empty($this->apiKey) && !empty($trackingNumber);
    }

    public function track(string $trackingNumber, array $options = []): array
    {
        if (empty($this->apiKey)) {
            return [
                'success' => false,
                'source' => $this->getName(),
                'message' => '17TRACK API key is not configured.',
            ];
        }

        $carrierCode = $options['carrier_code'] ?? null;

        try {
            // 1. Ensure tracking number is registered in 17TRACK
            $regPayload = [
                ['number' => $trackingNumber]
            ];
            if ($carrierCode) {
                $regPayload[0]['carrier'] = (int) $carrierCode;
            }

            Http::withHeaders([
                '17token' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(8)->post(self::BASE_URL . '/register', $regPayload);

            // 2. Fetch tracking details
            $response = Http::withHeaders([
                '17token' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(8)->post(self::BASE_URL . '/gettrackinfo', [
                ['number' => $trackingNumber]
            ]);

            if (!$response->successful()) {
                return [
                    'success' => false,
                    'source' => $this->getName(),
                    'message' => '17TRACK HTTP error: ' . $response->status(),
                ];
            }

            $body = $response->json();
            $accepted = $body['data']['accepted'][0] ?? null;

            if (!$accepted || empty($accepted['track'])) {
                return [
                    'success' => false,
                    'source' => $this->getName(),
                    'message' => 'No tracking records found on 17TRACK.',
                ];
            }

            $track = $accepted['track'];
            $latestEvent = $track['z0'] ?? null; // Latest checkpoint
            $events = $track['z1'] ?? [];       // All events

            $rawStatus = strtoupper($track['e'] ?? 'IN_TRANSIT'); // 17track state code
            $normalizedStatus = $this->map17TrackStatus($rawStatus);

            $location = $latestEvent['c'] ?? ($latestEvent['z'] ?? 'International Destination Network');
            $description = $latestEvent['z'] ?? 'Carrier checkpoint verified via 17TRACK';
            $timestamp = $latestEvent['a'] ?? now()->toIso8601String();

            return [
                'success' => true,
                'source' => $this->getName(),
                'carrier' => $track['b'] ?? ($options['carrier_name'] ?? 'Global Carrier'),
                'milestone' => $normalizedStatus['event_code'],
                'event_code' => $normalizedStatus['event_code'],
                'status_label' => $normalizedStatus['label'],
                'location' => $location,
                'description' => $description,
                'timestamp' => $timestamp,
                'raw_events' => $events,
            ];
        } catch (\Throwable $e) {
            Log::warning("17TRACK query exception for {$trackingNumber}: " . $e->getMessage());
            return [
                'success' => false,
                'source' => $this->getName(),
                'message' => $e->getMessage(),
            ];
        }
    }

    private function map17TrackStatus(string $status): array
    {
        return match ($status) {
            'DELIVERED', '40' => [
                'event_code' => 'delivered',
                'label' => 'Delivered',
            ],
            'OUT_FOR_DELIVERY', '35' => [
                'event_code' => 'out_for_delivery',
                'label' => 'Out for Delivery',
            ],
            'CUSTOMS', '30' => [
                'event_code' => 'customs_hold',
                'label' => 'Customs Clearance in Progress',
            ],
            'IN_TRANSIT', '20', 'TRANSIT' => [
                'event_code' => 'destination_facility_arrival',
                'label' => 'In Transit at Destination Facility',
            ],
            'PICKUP', '10' => [
                'event_code' => 'last_mile_handover',
                'label' => 'Handed over for Last Mile Delivery',
            ],
            'EXCEPTION', 'UNDELIVERED', '50' => [
                'event_code' => 'delivery_attempted',
                'label' => 'Delivery Attempted / Action Required',
            ],
            default => [
                'event_code' => 'destination_facility_arrival',
                'label' => 'In Transit to Destination Hub',
            ],
        };
    }
}
