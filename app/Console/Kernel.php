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
        // Run full backup daily at midnight
        $schedule->command('app:backup')
            ->dailyAt('00:00')
            ->onSuccess(function () {
                \App\Services\LoggingService::logSystemEvent('scheduled_backup_completed');
            })
            ->onFailure(function () {
                \App\Services\LoggingService::logError('scheduled_backup_failed');
            });

        // Run database backup every 6 hours
        $schedule->command('app:backup --type=db')
            ->everyFourHours()
            ->onSuccess(function () {
                \App\Services\LoggingService::logSystemEvent('scheduled_db_backup_completed');
            })
            ->onFailure(function () {
                \App\Services\LoggingService::logError('scheduled_db_backup_failed');
            });

        // Monitor backup health daily
        $schedule->command('backup:monitor')
            ->dailyAt('01:00')
            ->onFailure(function () {
                \App\Services\LoggingService::logError('backup_health_check_failed');
            });

        // Clean old backups weekly
        $schedule->command('backup:clean')
            ->weekly()
            ->onSuccess(function () {
                \App\Services\LoggingService::logSystemEvent('backup_cleanup_completed');
            })
            ->onFailure(function () {
                \App\Services\LoggingService::logError('backup_cleanup_failed');
            });
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
} 