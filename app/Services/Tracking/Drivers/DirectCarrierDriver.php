<?php

namespace App\Services\Tracking\Drivers;

use App\Services\Tracking\Contracts\CarrierTrackingDriverInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DirectCarrierDriver implements CarrierTrackingDriverInterface
{
    public function getName(): string
    {
        return 'direct_carrier';
    }

    public function supports(string $carrier, string $trackingNumber): bool
    {
        $carrierLower = strtolower(trim($carrier));
        return in_array($carrierLower, ['dhl', 'fedex', 'ups', 'aramex', 'usps', 'royal_mail', 'dpd', 'australia_post'])
            || $this->detectCarrierByFormat($trackingNumber) !== null;
    }

    /**
     * Auto-detect carrier by tracking number regex pattern
     */
    public function detectCarrierByFormat(string $trackingNumber): ?string
    {
        $clean = strtoupper(trim($trackingNumber));

        if (preg_match('/^1Z[0-9A-Z]{16}$/i', $clean)) {
            return 'ups';
        }
        if (preg_match('/^(\d{10}|\d{11})$/', $clean)) {
            return 'dhl';
        }
        if (preg_match('/^(\d{12}|\d{15}|\d{20})$/', $clean)) {
            return 'fedex';
        }
        if (preg_match('/^(94|93|92|94|95)\d{20}$/', $clean)) {
            return 'usps';
        }
        if (preg_match('/^[A-Z]{2}\d{9}[A-Z]{2}$/i', $clean)) {
            return str_ends_with($clean, 'GB') ? 'royal_mail' : 'universal_postal';
        }

        return null;
    }

    /**
     * Get direct tracking URL for the consignee to view on carrier portal
     */
    public function getDirectTrackingUrl(string $carrier, string $trackingNumber): string
    {
        $carrier = strtolower(trim($carrier ?: ($this->detectCarrierByFormat($trackingNumber) ?? 'carrier')));
        $clean = trim($trackingNumber);

        return match ($carrier) {
            'dhl', 'dhl_express' => "https://www.dhl.com/global-en/home/tracking/tracking-express.html?submit=1&tracking-id={$clean}",
            'fedex' => "https://www.fedex.com/fedextrack/?trknbr={$clean}",
            'ups' => "https://www.ups.com/track?tracknum={$clean}",
            'aramex' => "https://www.aramex.com/track/results?mode=0&ShipmentNumber={$clean}",
            'usps' => "https://tools.usps.com/go/TrackConfirmAction?tLabels={$clean}",
            'royal_mail' => "https://www.royalmail.com/track-your-item#/tracking-results/{$clean}",
            'dpd' => "https://tracking.dpd.de/status/en_US/parcel/{$clean}",
            default => "https://www.17track.net/en/track?nums={$clean}",
        };
    }

    public function track(string $trackingNumber, array $options = []): array
    {
        $carrier = strtolower(trim($options['carrier_name'] ?? ($this->detectCarrierByFormat($trackingNumber) ?? 'dhl')));
        $directUrl = $this->getDirectTrackingUrl($carrier, $trackingNumber);

        // Check if DHL Express Unified Tracking API credentials exist
        if ($carrier === 'dhl' && config('services.dhl.api_key')) {
            $result = $this->queryDhlApi($trackingNumber);
            if ($result['success']) {
                $result['direct_url'] = $directUrl;
                return $result;
            }
        }

        // Direct API credentials not configured or query not supported
        return [
            'success' => false,
            'source' => $this->getName(),
            'message' => 'Direct carrier API credentials not configured or unsupported. Falling back to autonomous tracking gateway.',
            'direct_url' => $directUrl,
        ];
    }

    private function queryDhlApi(string $trackingNumber): array
    {
        try {
            $apiKey = config('services.dhl.api_key');
            $response = Http::withHeaders([
                'DHL-API-Key' => $apiKey,
                'Accept' => 'application/json',
            ])->timeout(6)->get("https://api-eu.dhl.com/track/shipments", [
                'trackingNumber' => $trackingNumber,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $shipment = $data['shipments'][0] ?? null;
                if ($shipment && !empty($shipment['status'])) {
                    $status = $shipment['status'];
                    $statusCode = strtoupper($status['statusCode'] ?? 'transit');

                    $eventCode = match ($statusCode) {
                        'DELIVERED' => 'delivered',
                        'OUT_FOR_DELIVERY' => 'out_for_delivery',
                        'CUSTOMS' => 'customs_hold',
                        default => 'destination_facility_arrival',
                    };

                    return [
                        'success' => true,
                        'source' => 'dhl_api',
                        'carrier' => 'DHL Express',
                        'event_code' => $eventCode,
                        'milestone' => $eventCode,
                        'status_label' => $status['status'] ?? 'In Transit',
                        'location' => $status['location']['address']['addressLocality'] ?? 'Destination Facility',
                        'description' => $status['description'] ?? 'DHL Express checkpoint verified',
                        'timestamp' => $status['timestamp'] ?? now()->toIso8601String(),
                    ];
                }
            }
        } catch (\Throwable $e) {
            Log::info("DHL API query notice for {$trackingNumber}: " . $e->getMessage());
        }

        return ['success' => false];
    }
}
