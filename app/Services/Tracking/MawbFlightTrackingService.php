<?php

namespace App\Services\Tracking;

use App\Models\MAWB;
use App\Services\AutomatedTrackingService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MawbFlightTrackingService
{
    /**
     * Standard IATA 3-digit Airline Prefix to Airline Dictionary
     */
    private const AIRLINE_PREFIXES = [
        '285' => ['code' => 'RA', 'name' => 'Nepal Airlines', 'hub' => 'KTM', 'country' => 'Nepal'],
        '157' => ['code' => 'QR', 'name' => 'Qatar Airways Cargo', 'hub' => 'DOH', 'country' => 'Qatar'],
        '176' => ['code' => 'EK', 'name' => 'Emirates SkyCargo', 'hub' => 'DXB', 'country' => 'UAE'],
        '141' => ['code' => 'FZ', 'name' => 'FlyDubai Cargo', 'hub' => 'DXB', 'country' => 'UAE'],
        '098' => ['code' => 'AI', 'name' => 'Air India Cargo', 'hub' => 'DEL', 'country' => 'India'],
        '020' => ['code' => 'LH', 'name' => 'Lufthansa Cargo', 'hub' => 'FRA', 'country' => 'Germany'],
        '125' => ['code' => 'BA', 'name' => 'British Airways World Cargo', 'hub' => 'LHR', 'country' => 'United Kingdom'],
        '074' => ['code' => 'KL', 'name' => 'KLM Cargo', 'hub' => 'AMS', 'country' => 'Netherlands'],
        '057' => ['code' => 'AF', 'name' => 'Air France Cargo', 'hub' => 'CDG', 'country' => 'France'],
        '618' => ['code' => 'SQ', 'name' => 'Singapore Airlines Cargo', 'hub' => 'SIN', 'country' => 'Singapore'],
        '217' => ['code' => 'TG', 'name' => 'Thai Airways Cargo', 'hub' => 'BKK', 'country' => 'Thailand'],
        '072' => ['code' => 'GF', 'name' => 'Gulf Air Cargo', 'hub' => 'BAH', 'country' => 'Bahrain'],
        '160' => ['code' => 'CX', 'name' => 'Cathay Pacific Cargo', 'hub' => 'HKG', 'country' => 'Hong Kong'],
        '014' => ['code' => 'AC', 'name' => 'Air Canada Cargo', 'hub' => 'YYZ', 'country' => 'Canada'],
        '001' => ['code' => 'AA', 'name' => 'American Airlines Cargo', 'hub' => 'DFW', 'country' => 'USA'],
        '006' => ['code' => 'DL', 'name' => 'Delta Cargo', 'hub' => 'ATL', 'country' => 'USA'],
        '016' => ['code' => 'UA', 'name' => 'United Cargo', 'hub' => 'ORD', 'country' => 'USA'],
        '205' => ['code' => 'ANA', 'name' => 'All Nippon Airways Cargo', 'hub' => 'NRT', 'country' => 'Japan'],
        '784' => ['code' => 'CZ', 'name' => 'China Southern Cargo', 'hub' => 'CAN', 'country' => 'China'],
    ];

    public function __construct(private readonly AutomatedTrackingService $automatedTracking)
    {
    }

    /**
     * Resolve airline details from MAWB number (Format: XXX-XXXXXXXX or XXXXXXXXXXX)
     */
    public function resolveAirlineByMawb(string $mawbNumber): array
    {
        $clean = preg_replace('/[^0-9]/', '', $mawbNumber);
        $prefix = substr($clean, 0, 3);
        $serial = substr($clean, 3);

        $info = self::AIRLINE_PREFIXES[$prefix] ?? [
            'code' => 'CARGO',
            'name' => 'International Air Cargo Carrier',
            'hub' => 'DXB',
            'country' => 'Transit Hub',
        ];

        return array_merge($info, [
            'prefix' => $prefix,
            'serial' => $serial,
            'tracking_url' => $this->getAirCargoTrackingUrl($prefix, $serial),
        ]);
    }

    /**
     * Get direct air cargo tracking URL
     */
    public function getAirCargoTrackingUrl(string $prefix, string $serial): string
    {
        return match ($prefix) {
            '176' => "https://www.skycargo.com/services/track-shipment/?awb={$prefix}-{$serial}",
            '157' => "https://www.qrcargo.com/track-shipment?awbNumber={$prefix}-{$serial}",
            '020' => "https://lufthansa-cargo.com/tracking?awb={$prefix}-{$serial}",
            '125' => "https://www.iagcargo.com/en/track?awb={$prefix}-{$serial}",
            '098' => "https://airindia.cargo.aero/tracking?prefix={$prefix}&number={$serial}",
            default => "https://www.track-trace.com/aircargo#{$prefix}-{$serial}",
        };
    }

    /**
     * Query live flight status via AviationStack API (Free Tier)
     */
    public function queryAviationStack(?string $airlineIata, ?string $flightNumber): ?array
    {
        $apiKey = config('services.aviationstack.key', env('AVIATIONSTACK_KEY'));
        if (empty($apiKey) || empty($airlineIata) || empty($flightNumber)) {
            return null;
        }

        try {
            $cleanFlight = preg_replace('/[^0-9]/', '', $flightNumber);
            $response = Http::timeout(5)->get('http://api.aviationstack.com/v1/flights', [
                'access_key' => $apiKey,
                'airline_iata' => strtoupper($airlineIata),
                'flight_number' => $cleanFlight,
                'limit' => 1,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $flight = $data['data'][0] ?? null;
                if ($flight) {
                    return [
                        'status' => $flight['flight_status'] ?? 'active',
                        'departure_airport' => $flight['departure']['airport'] ?? 'Kathmandu (KTM)',
                        'departure_scheduled' => $flight['departure']['scheduled'] ?? null,
                        'departure_actual' => $flight['departure']['actual'] ?? null,
                        'arrival_airport' => $flight['arrival']['airport'] ?? 'Transit Hub',
                        'arrival_scheduled' => $flight['arrival']['scheduled'] ?? null,
                        'arrival_estimated' => $flight['arrival']['estimated'] ?? null,
                        'live' => $flight['live'] ?? null,
                    ];
                }
            }
        } catch (\Throwable $e) {
            Log::info("AviationStack query notice: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Synchronize and advance a single active MAWB and cascade to all child shipments
     */
    public function syncMawb(MAWB $mawb): array
    {
        $airlineInfo = $this->resolveAirlineByMawb($mawb->mawb_number);
        $airlineCode = $mawb->airline_code ?: $airlineInfo['code'];
        $flightNumber = $mawb->flight_number;
        $originAirport = $mawb->origin_airport ?: 'KTM';
        $destAirport = $mawb->destination_airport ?: ($mawb->hub?->hub_code ?? $airlineInfo['hub']);

        // Check if AviationStack returns live flight status
        $flightTelemetry = $this->queryAviationStack($airlineCode, $flightNumber);

        $now = now();
        $flightDate = $mawb->flight_date ? Carbon::parse($mawb->flight_date) : $mawb->created_at;
        $hoursSinceFlight = $flightDate ? $flightDate->diffInHours($now, false) : 0;

        $targetStatus = $mawb->status;
        $notes = null;

        if ($flightTelemetry) {
            $liveStatus = strtolower($flightTelemetry['status']);
            if (in_array($liveStatus, ['active', 'airborne', 'en-route'])) {
                $targetStatus = 'in_transit';
                $notes = "Live Flight {$airlineCode} {$flightNumber} is airborne en route from {$originAirport} to {$destAirport}.";
            } elseif (in_array($liveStatus, ['landed', 'arrived'])) {
                $targetStatus = 'cleared';
                $notes = "Flight {$airlineCode} {$flightNumber} touched down at {$destAirport}. Air cargo customs breakdown started.";
            }
        } else {
            // Autonomous schedule-based progression
            // Flights out of KTM typically take 4-8 hours to transit hubs (DXB, DOH, DEL)
            if ($mawb->status === 'assigned' || $mawb->status === 'unused') {
                if ($hoursSinceFlight >= 0) {
                    $targetStatus = 'in_transit';
                    $notes = "Consolidated cargo departed {$originAirport} on {$airlineInfo['name']} flight {$airlineCode} {$flightNumber}.";
                }
            } elseif ($mawb->status === 'in_transit') {
                if ($hoursSinceFlight >= 8) {
                    $targetStatus = 'cleared';
                    $notes = "Flight arrived at {$destAirport} Air Cargo Terminal. Destination import customs inspection initiated.";
                }
            } elseif ($mawb->status === 'cleared') {
                if ($hoursSinceFlight >= 24) {
                    $targetStatus = 'completed';
                    $notes = "Customs clearance finalized at {$destAirport}. Consignments released to regional last-mile distribution networks.";
                }
            }
        }

        $cascaded = false;
        if ($targetStatus !== $mawb->status) {
            $mawb->status = $targetStatus;
            $mawb->save();

            // Cascade to all attached consignments
            $this->automatedTracking->cascadeMawbMilestone(
                $mawb,
                $targetStatus,
                "{$destAirport} International Cargo Hub",
                $notes
            );
            $cascaded = true;
        }

        return [
            'success' => true,
            'mawb_id' => $mawb->id,
            'mawb_number' => $mawb->mawb_number,
            'status' => $mawb->status,
            'cascaded' => $cascaded,
            'airline' => $airlineInfo['name'],
            'tracking_url' => $airlineInfo['tracking_url'],
            'notes' => $notes,
        ];
    }

    /**
     * Batch synchronize all active MAWBs in the system
     */
    public function syncAllActiveMawbs(): array
    {
        $activeMawbs = MAWB::whereIn('status', ['assigned', 'in_transit', 'cleared'])
            ->get();

        $synced = 0;
        $updated = 0;

        foreach ($activeMawbs as $mawb) {
            $res = $this->syncMawb($mawb);
            $synced++;
            if (!empty($res['cascaded'])) {
                $updated++;
            }
        }

        return [
            'total_active_mawbs' => $synced,
            'updated_mawbs' => $updated,
        ];
    }
}
