<?php

namespace App\Services;

use App\Mail\ShipmentStatusNotificationMail;
use App\Models\DomesticShipment;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class ShipmentScanService
{
    /**
     * A scan records an operational milestone. Several milestones can map to
     * the same customer-facing status without losing the physical scan trail.
     */
    private const EVENTS = [
        'booking_confirmed' => ['status' => 'confirmed', 'label' => 'Booking confirmed', 'icon' => 'fa-circle-check', 'modes' => ['domestic', 'international'], 'from' => ['pending']],
        'booking_done' => ['status' => 'confirmed', 'label' => 'Bookings Done', 'icon' => 'fa-receipt', 'modes' => ['international'], 'from' => ['pending', 'confirmed']],
        'shipment_prepared' => ['status' => 'processing', 'label' => 'Shipment prepared', 'icon' => 'fa-boxes-packing', 'modes' => ['international'], 'from' => ['confirmed']],
        'packaging_completed' => ['status' => 'processing', 'label' => 'Packaging Completed', 'icon' => 'fa-box-archive', 'modes' => ['international'], 'from' => ['pending', 'confirmed', 'processing']],
        'shipment_picked_up' => ['status' => 'picked_up', 'label' => 'Shipment picked up', 'icon' => 'fa-box', 'modes' => ['domestic', 'international'], 'from' => ['confirmed', 'processing']],
        'origin_facility_arrival' => ['status' => 'in_transit', 'label' => 'Received at origin facility', 'icon' => 'fa-warehouse', 'modes' => ['domestic', 'international'], 'from' => ['picked_up', 'processing', 'confirmed']],
        'origin_facility_departure' => ['status' => 'in_transit', 'label' => 'Departed origin facility', 'icon' => 'fa-truck-arrow-right', 'modes' => ['domestic', 'international'], 'from' => ['in_transit', 'picked_up', 'processing', 'confirmed']],
        'export_cleared' => ['status' => 'in_transit', 'label' => 'Export Clearance Done', 'icon' => 'fa-stamp', 'modes' => ['international'], 'from' => ['confirmed', 'processing', 'picked_up', 'in_transit']],
        'export_departure' => ['status' => 'in_transit', 'label' => 'Departed export gateway', 'icon' => 'fa-plane-departure', 'modes' => ['international'], 'from' => ['in_transit']],
        'in_transit_airline' => ['status' => 'in_transit', 'label' => 'In Transit to Hub by Airlines', 'icon' => 'fa-plane-departure', 'modes' => ['international'], 'from' => ['confirmed', 'processing', 'in_transit']],
        'arrival_notice' => ['status' => 'in_transit', 'label' => 'Arrival Notice (At Hub)', 'icon' => 'fa-plane-arrival', 'modes' => ['international'], 'from' => ['in_transit']],
        'non_arrival_exception' => ['status' => 'in_transit', 'label' => 'Non-Arrival Packet Notice', 'icon' => 'fa-triangle-exclamation', 'modes' => ['international'], 'from' => ['in_transit']],
        'import_arrival' => ['status' => 'in_transit', 'label' => 'Arrived at import gateway', 'icon' => 'fa-plane-arrival', 'modes' => ['international'], 'from' => ['in_transit']],
        'customs_hold' => ['status' => 'customs_clearance', 'label' => 'Customs review', 'icon' => 'fa-passport', 'modes' => ['international'], 'from' => ['in_transit']],
        'customs_cleared' => ['status' => 'in_transit', 'label' => 'Customs cleared', 'icon' => 'fa-stamp', 'modes' => ['international'], 'from' => ['customs_clearance']],
        'import_cleared' => ['status' => 'in_transit', 'label' => 'Import Clearance Completed', 'icon' => 'fa-passport', 'modes' => ['international'], 'from' => ['in_transit', 'customs_clearance']],
        'transit_facility_arrival' => ['status' => 'in_transit', 'label' => 'Arrived at transit facility', 'icon' => 'fa-building-circle-arrow-right', 'modes' => ['domestic', 'international'], 'from' => ['in_transit']],
        'transit_facility_departure' => ['status' => 'in_transit', 'label' => 'Departed transit facility', 'icon' => 'fa-route', 'modes' => ['domestic', 'international'], 'from' => ['in_transit']],
        'destination_facility_arrival' => ['status' => 'in_transit', 'label' => 'Arrived at destination facility', 'icon' => 'fa-warehouse', 'modes' => ['domestic', 'international'], 'from' => ['in_transit']],
        'last_mile_handover' => ['status' => 'out_for_delivery', 'label' => 'Handed over for Last Mile Delivery', 'icon' => 'fa-truck-arrow-right', 'modes' => ['international'], 'from' => ['in_transit', 'customs_clearance', 'processing']],
        'out_for_delivery' => ['status' => 'out_for_delivery', 'label' => 'Out for delivery', 'icon' => 'fa-motorcycle', 'modes' => ['domestic', 'international'], 'from' => ['in_transit', 'customs_clearance', 'failed_delivery']],
        'delivered' => ['status' => 'delivered', 'label' => 'Delivered', 'icon' => 'fa-circle-check', 'modes' => ['domestic', 'international'], 'from' => ['out_for_delivery', 'in_transit']],
        'delivery_attempted' => ['status' => 'failed_delivery', 'label' => 'Delivery attempted', 'icon' => 'fa-triangle-exclamation', 'modes' => ['domestic', 'international'], 'from' => ['out_for_delivery']],
        'returned_to_sender' => ['status' => 'returned', 'label' => 'Returned to sender', 'icon' => 'fa-arrow-rotate-left', 'modes' => ['domestic', 'international']],
        'cancelled' => ['status' => 'cancelled', 'label' => 'Shipment cancelled', 'icon' => 'fa-circle-xmark', 'modes' => ['domestic', 'international']],
    ];

    private const TRANSITIONS = [
        'pending' => ['confirmed', 'cancelled', 'processing'],
        'confirmed' => ['processing', 'picked_up', 'in_transit', 'cancelled'],
        'processing' => ['picked_up', 'in_transit', 'out_for_delivery', 'cancelled'],
        'picked_up' => ['in_transit', 'failed_delivery', 'returned'],
        'in_transit' => ['customs_clearance', 'out_for_delivery', 'failed_delivery', 'returned', 'in_transit', 'delivered'],
        'customs_clearance' => ['in_transit', 'out_for_delivery', 'returned'],
        'out_for_delivery' => ['delivered', 'failed_delivery', 'returned'],
        'failed_delivery' => ['out_for_delivery', 'returned'],
        'delivered' => [],
        'returned' => [],
        'cancelled' => [],
    ];

    public function availableEvents(Model $shipment): array
    {
        $mode = $this->mode($shipment);

        return collect(self::EVENTS)
            ->filter(fn (array $event) => in_array($mode, $event['modes'], true))
            ->filter(fn (array $event) => $this->canRecord((string) $shipment->status, $event))
            ->map(fn (array $event, string $code) => [
                'code' => $code,
                'status' => $event['status'],
                'label' => $event['label'],
                'icon' => $event['icon'],
            ])->values()->all();
    }

    public function record(
        Model $shipment,
        string $eventCode,
        ?string $location,
        ?string $notes,
        User $actor,
        string $source = 'manual',
        ?string $customTimestamp = null,
        array $extraMeta = []
    ): Model {
        $definition = self::EVENTS[$eventCode] ?? null;

        if (! $definition || ! in_array($this->mode($shipment), $definition['modes'], true)) {
            throw ValidationException::withMessages([
                'event_code' => 'This scan event is not valid for the selected shipment service.',
            ]);
        }

        return DB::transaction(function () use ($shipment, $eventCode, $definition, $location, $notes, $actor, $source, $customTimestamp, $extraMeta) {
            /** @var Model $locked */
            $locked = $shipment->newQuery()->lockForUpdate()->findOrFail($shipment->getKey());
            $newStatus = $definition['status'];

            if (! $this->canRecord((string) $locked->status, $definition)) {
                throw ValidationException::withMessages([
                    'event_code' => "{$definition['label']} cannot follow the current shipment status.",
                ]);
            }

            $eventTime = $customTimestamp ? \Carbon\Carbon::parse($customTimestamp)->toIso8601String() : now()->toIso8601String();

            $event = [
                'event_code' => $eventCode,
                'status' => $newStatus,
                'status_label' => $definition['label'],
                'icon' => $definition['icon'],
                'description' => $notes ?: $definition['label'],
                'location' => $location ?: 'NETPACK scan point',
                'time' => $eventTime,
                'scan_source' => $source,
                'scanned_by_user_id' => $actor->id,
                'scanned_by_role' => $actor->user_type ?? 'staff',
                'meta' => $extraMeta,
            ];

            if ($locked instanceof DomesticShipment) {
                $locked->trackingEvents()->create([
                    'status' => $newStatus,
                    'location' => $event['location'],
                    'description' => $event['description'],
                    'additional_data' => collect($event)->except(['status', 'location', 'description', 'time'])->all(),
                    'event_time' => $customTimestamp ? \Carbon\Carbon::parse($customTimestamp) : now(),
                ]);

                $history = $locked->tracking_history ?? [];
                $history[] = $event;
                $updates = [
                    'status' => $newStatus,
                    'tracking_history' => $history,
                    'delivery_notes' => $notes,
                ];

                if ($newStatus === 'delivered') {
                    $updates['actual_delivery_at'] = now();
                }

                $locked->update($updates);
            } elseif ($locked instanceof Shipment) {
                $history = $locked->tracking_history ?? [];
                $history[] = $event;
                $updates = [
                    'status' => $newStatus,
                    'tracking_history' => $history,
                    'current_location' => $event['location'],
                    'status_notes' => $notes,
                    'agency_milestone' => $eventCode,
                ];

                if (!empty($extraMeta['mawb_number'])) {
                    $updates['mawb_number'] = $extraMeta['mawb_number'];
                }
                if (!empty($extraMeta['last_mile_carrier_name'])) {
                    $updates['last_mile_carrier_name'] = $extraMeta['last_mile_carrier_name'];
                }
                if (!empty($extraMeta['last_mile_tracking_number'])) {
                    $updates['last_mile_tracking_number'] = $extraMeta['last_mile_tracking_number'];
                }

                if (in_array($eventCode, ['customs_cleared', 'import_cleared'], true)) {
                    $updates['customs_cleared_at'] = now();
                    $updates['customs_status'] = 'cleared';
                }
                if ($eventCode === 'arrival_notice') {
                    $updates['arrived_at_agency'] = $customTimestamp ? \Carbon\Carbon::parse($customTimestamp) : now();
                }
                if ($newStatus === 'delivered') {
                    $updates['delivered_at'] = $customTimestamp ? \Carbon\Carbon::parse($customTimestamp) : now();
                }

                $locked->update($updates);

                // Send professional client email notification
                $this->notifyClientSafely(
                    $locked,
                    $definition['label'],
                    $event['description'],
                    $event['location'],
                    $eventTime,
                    $notes,
                    $updates['mawb_number'] ?? $locked->mawb_number,
                    $updates['last_mile_carrier_name'] ?? $locked->last_mile_carrier_name,
                    $updates['last_mile_tracking_number'] ?? $locked->last_mile_tracking_number
                );
            } else {
                throw ValidationException::withMessages(['event_code' => 'Unsupported shipment record.']);
            }

            return $locked->fresh();
        });
    }

    public function canTransition(string $from, string $to): bool
    {
        return $from === $to || in_array($to, self::TRANSITIONS[$from] ?? [], true);
    }

    private function canRecord(string $from, array $event): bool
    {
        return $this->canTransition($from, $event['status'])
            && (! isset($event['from']) || in_array($from, $event['from'], true));
    }

    public function eventCodeForStatus(string $status): ?string
    {
        return collect(self::EVENTS)
            ->search(fn (array $event) => $event['status'] === $status) ?: null;
    }

    private function mode(Model $shipment): string
    {
        if ($shipment instanceof DomesticShipment) {
            return 'domestic';
        }

        if ($shipment instanceof Shipment) {
            return $shipment->shipment_type === 'domestic' ? 'domestic' : 'international';
        }

        return 'domestic';
    }

    /**
     * Safely dispatch client email notification without throwing unhandled exceptions
     */
    private function notifyClientSafely(
        Shipment $shipment,
        string $statusLabel,
        string $eventDescription,
        ?string $location,
        string $timestamp,
        ?string $notes = null,
        ?string $mawbNumber = null,
        ?string $carrierName = null,
        ?string $carrierTracking = null
    ): void {
        try {
            $recipientEmail = $shipment->customer?->email ?? ($shipment->sender_email ?? ($shipment->receiver_email ?? null));

            if ($recipientEmail && filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
                Mail::to($recipientEmail)->send(new ShipmentStatusNotificationMail(
                    $shipment,
                    $statusLabel,
                    $eventDescription,
                    $location,
                    $timestamp,
                    $notes,
                    $mawbNumber,
                    $carrierName,
                    $carrierTracking
                ));
            }
        } catch (\Throwable $e) {
            Log::warning('Shipment status email notification failed: ' . $e->getMessage(), [
                'shipment_id' => $shipment->id,
                'tracking_number' => $shipment->tracking_number,
            ]);
        }
    }
}
