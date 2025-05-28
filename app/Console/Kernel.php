<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Daily database backup at 2:00 AM with encryption and S3
        $schedule->command('app:production-backup --type=db --notify')
            ->dailyAt('02:00')
            ->appendOutputTo(storage_path('logs/backup.log'))
            ->emailOutputOnFailure(env('ADMIN_EMAIL'));

        // Weekly full backup on Sundays at 3:00 AM
        $schedule->command('app:production-backup --type=full --notify')
            ->weeklyOn(0, '03:00')
            ->appendOutputTo(storage_path('logs/backup.log'))
            ->emailOutputOnFailure(env('ADMIN_EMAIL'));

        // Daily backup health monitoring at 8:00 AM
        $schedule->command('backup:monitor')
            ->dailyAt('08:00')
            ->emailOutputOnFailure(env('ADMIN_EMAIL'));

        // Weekly backup cleanup on Mondays at 4:00 AM
        $schedule->command('backup:clean')
            ->weeklyOn(1, '04:00')
            ->appendOutputTo(storage_path('logs/backup.log'));
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
