<?php

namespace App\Services\Tracking\Contracts;

interface CarrierTrackingDriverInterface
{
    /**
     * Query tracking information for a tracking code.
     *
     * @param string $trackingNumber
     * @param array $options
     * @return array
     */
    public function track(string $trackingNumber, array $options = []): array;

    /**
     * Check if this driver supports the given carrier or tracking number format.
     *
     * @param string $carrier
     * @param string $trackingNumber
     * @return bool
     */
    public function supports(string $carrier, string $trackingNumber): bool;

    /**
     * Get the driver unique identifier.
     *
     * @return string
     */
    public function getName(): string;
}
