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
        $schedule->command('app:working-backup --type=db --encrypt --s3 --notify')
            ->dailyAt('02:00')
            ->appendOutputTo(storage_path('logs/backup.log'))
            ->emailOutputOnFailure(env('ADMIN_EMAIL'));

        // Weekly full backup on Sundays at 3:00 AM
        $schedule->command('app:working-backup --type=full --encrypt --s3 --notify')
            ->weeklyOn(0, '03:00')
            ->appendOutputTo(storage_path('logs/backup.log'))
            ->emailOutputOnFailure(env('ADMIN_EMAIL'));

        // Daily backup verification at 8:00 AM
        $schedule->command('app:working-restore --list')
            ->dailyAt('08:00')
            ->appendOutputTo(storage_path('logs/backup.log'));

        // Weekly S3 backup verification on Tuesdays at 9:00 AM
        $schedule->command('app:working-restore --s3')
            ->weeklyOn(2, '09:00')
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
