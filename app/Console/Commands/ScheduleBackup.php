<?php

namespace App\Console\Commands;

use App\Services\LoggingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class ScheduleBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:backup {--type=all : Type of backup (all, files, db)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the backup process';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $type = $this->option('type');

        try {
            $this->info('Starting backup process...');
            LoggingService::logSystemEvent('backup_started', ['type' => $type]);

            switch ($type) {
                case 'files':
                    $this->info('Running files only backup...');
                    Artisan::call('backup:run --only-files');
                    break;

                case 'db':
                    $this->info('Running database only backup...');
                    Artisan::call('backup:run --only-db');
                    break;

                default:
                    $this->info('Running full backup...');
                    Artisan::call('backup:run');
                    break;
            }

            $this->info('Backup completed successfully!');
            LoggingService::logSystemEvent('backup_completed', [
                'type' => $type,
                'status' => 'success'
            ]);

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Backup failed: ' . $e->getMessage());
            LoggingService::logError('Backup failed', [
                'type' => $type,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return Command::FAILURE;
        }
    }
} 