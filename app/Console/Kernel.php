<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\ProcessDeliveryReminders::class,
        \App\Console\Commands\ScheduleAllReminders::class,
        \App\Console\Commands\CleanupReminders::class,
        \App\Console\Commands\ProductionReadinessCheck::class,
        \App\Console\Commands\CreateInitialAdmin::class,
        \App\Console\Commands\SyncAutomatedTrackingCommand::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Automated tracking synchronization for active MAWBs and last-mile delivery carriers
        $schedule->command('tracking:sync-all')->everyFifteenMinutes();

        // Run every 10 minutes to check for pending reminders
        $schedule->command('reminders:process')->everyTenMinutes();
        
        // Also run on specific hours for critical checks
        $schedule->command('reminders:process')->hourly();
        
        // Schedule reminders for all pending pickups (daily at 6 AM)
        $schedule->command('reminders:schedule-all')->dailyAt('06:00');
        
        // Run once at midnight for cleanup
        $schedule->command('reminders:cleanup')->daily();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
