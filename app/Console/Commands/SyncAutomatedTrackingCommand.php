<?php

namespace App\Console\Commands;

use App\Models\Shipment;
use App\Services\AutomatedTrackingService;
use App\Services\CarrierTrackingSyncService;
use App\Services\Tracking\MawbFlightTrackingService;
use Illuminate\Console\Command;

class SyncAutomatedTrackingCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tracking:sync-all {--shipment= : Specific shipment tracking number to sync}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize all active international last-mile carriers, MAWB flights, and operational tracking telemetry.';

    public function handle(
        CarrierTrackingSyncService $carrierSync,
        MawbFlightTrackingService $mawbSync,
        AutomatedTrackingService $automatedTracking
    ): int {
        $this->info('Starting automated tracking telemetry synchronization...');

        $specificTracking = $this->option('shipment');
        if ($specificTracking) {
            $shipment = Shipment::where('tracking_number', strtoupper(trim($specificTracking)))
                ->orWhere('hawb_number', strtoupper(trim($specificTracking)))
                ->first();

            if (!$shipment) {
                $this->error("Shipment {$specificTracking} not found.");
                return Command::FAILURE;
            }

            $res = $carrierSync->syncShipment($shipment);
            $this->line("Result: " . json_encode($res));
            return Command::SUCCESS;
        }

        // 1. Synchronize Master Air Waybills (Air Cargo Flights)
        $this->comment('Synchronizing active Master Air Waybill (MAWB) flights...');
        $mawbResult = $mawbSync->syncAllActiveMawbs();
        $this->info("MAWB Sync Complete: {$mawbResult['total_active_mawbs']} active checked, {$mawbResult['updated_mawbs']} cascaded.");

        // 2. Synchronize Global Last-Mile Delivery Carriers (DHL, FedEx, UPS, Aramex, 17Track)
        $this->comment('Synchronizing active last-mile delivery carrier consignments...');
        $carrierResult = $carrierSync->syncAllActiveShipments();
        $this->info("Carrier Sync Complete: {$carrierResult['total_checked']} active checked, {$carrierResult['updated_count']} updated, {$carrierResult['error_count']} errors.");

        // 3. Ensure any freshly booked shipments without tracking history have their structured booking milestone
        $pendingWithoutHistory = Shipment::whereNull('tracking_history')
            ->orWhere('tracking_history', '[]')
            ->take(50)
            ->get();

        $seeded = 0;
        foreach ($pendingWithoutHistory as $s) {
            $automatedTracking->recordBookingPlaced($s);
            $seeded++;
        }
        if ($seeded > 0) {
            $this->info("Initialized structured booking milestones for {$seeded} newly created consignments.");
        }

        $this->info('Automated tracking telemetry synchronization completed successfully.');
        return Command::SUCCESS;
    }
}
