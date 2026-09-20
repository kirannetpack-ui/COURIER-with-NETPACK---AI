<?php

namespace App\Services\Tracking;

use App\Services\Tracking\Contracts\CarrierTrackingDriverInterface;
use App\Services\Tracking\Drivers\DirectCarrierDriver;
use App\Services\Tracking\Drivers\SeventeenTrackDriver;
use App\Services\Tracking\Drivers\SmartAutonomousDriver;

class CarrierTrackingGateway
{
    /** @var CarrierTrackingDriverInterface[] */
    private array $drivers = [];
    private DirectCarrierDriver $directDriver;

    public function __construct()
    {
        $this->directDriver = new DirectCarrierDriver();

        // 1. 17TRACK Driver (if API key available in env or config)
        $seventeenKey = config('services.17track.key', env('SEVENTEEN_TRACK_KEY'));
        if (!empty($seventeenKey)) {
            $this->drivers[] = new SeventeenTrackDriver($seventeenKey);
        }

        // 2. Direct Carrier Driver (DHL, FedEx, UPS official endpoints)
        $this->drivers[] = $this->directDriver;

        // 3. Smart Autonomous Fallback Driver
        $this->drivers[] = new SmartAutonomousDriver();
    }

    /**
     * Track a consignment across available drivers with priority
     */
    public function track(string $trackingNumber, array $options = []): array
    {
        $carrier = $options['carrier_name'] ?? ($this->directDriver->detectCarrierByFormat($trackingNumber) ?? 'carrier');

        foreach ($this->drivers as $driver) {
            if ($driver->supports($carrier, $trackingNumber)) {
                $res = $driver->track($trackingNumber, $options);
                if (!empty($res['success'])) {
                    // Enrich with direct tracking URL
                    if (empty($res['direct_url'])) {
                        $res['direct_url'] = $this->directDriver->getDirectTrackingUrl($carrier, $trackingNumber);
                    }
                    return $res;
                }
            }
        }

        // Fallback
        $res = (new SmartAutonomousDriver())->track($trackingNumber, $options);
        if (empty($res['direct_url'])) {
            $res['direct_url'] = $this->directDriver->getDirectTrackingUrl($carrier, $trackingNumber);
        }
        return $res;
    }

    /**
     * Get direct tracking portal URL for any carrier
     */
    public function getCarrierUrl(?string $carrier, string $trackingNumber): string
    {
        return $this->directDriver->getDirectTrackingUrl($carrier ?: '', $trackingNumber);
    }

    /**
     * Detect carrier name from tracking format
     */
    public function detectCarrier(string $trackingNumber): ?string
    {
        return $this->directDriver->detectCarrierByFormat($trackingNumber);
    }
}
