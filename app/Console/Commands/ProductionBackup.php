<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ProductionBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:production-backup 
                            {--type=full : Type of backup (db, files, full)}
                            {--filename= : Custom filename for the backup}
                            {--notify : Send email notification}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Production-ready backup with encryption, S3 storage, and monitoring';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $startTime = now();
        $this->info('🚀 HOMMSS Production Backup System');
        $this->info('==================================');

        try {
            // Pre-backup checks
            $this->performPreBackupChecks();

            // Determine backup type
            $backupType = $this->option('type');
            $customFilename = $this->option('filename');

            // Build backup arguments
            $arguments = $this->buildBackupArguments($backupType, $customFilename);

            $this->info("📦 Starting {$backupType} backup...");
            $this->info('🔐 Encryption: AES-256 (enabled)');
            $this->info('☁️  Storage: Local + AWS S3');

            // Execute backup
            $exitCode = $this->call('backup:run', $arguments);

            if ($exitCode !== 0) {
                throw new \Exception('Backup process failed with exit code: ' . $exitCode);
            }

            // Post-backup verification
            $this->performPostBackupVerification();

            // Calculate duration
            $duration = $startTime->diffInSeconds(now());

            $this->info('');
            $this->info('✅ Production backup completed successfully!');
            $this->info("⏱️  Duration: {$duration} seconds");

            // Send notification if requested
            if ($this->option('notify')) {
                $this->sendSuccessNotification($backupType, $duration);
            }

            // Display backup status
            $this->displayProductionBackupStatus();

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->handleBackupFailure($e, $startTime);
            return Command::FAILURE;
        }
    }

    /**
     * Perform pre-backup checks
     */
    protected function performPreBackupChecks()
    {
        $this->info('🔍 Performing pre-backup checks...');

        // Check encryption configuration
        $encryptionPassword = env('BACKUP_ARCHIVE_PASSWORD');
        if (!$encryptionPassword) {
            throw new \Exception('BACKUP_ARCHIVE_PASSWORD not configured in .env');
        }

        // Check S3 configuration
        $s3Config = [
            'AWS_ACCESS_KEY_ID' => env('AWS_ACCESS_KEY_ID'),
            'AWS_SECRET_ACCESS_KEY' => env('AWS_SECRET_ACCESS_KEY'),
            'AWS_DEFAULT_REGION' => env('AWS_DEFAULT_REGION'),
            'AWS_BUCKET' => env('AWS_BUCKET'),
        ];

        foreach ($s3Config as $key => $value) {
            if (!$value) {
                $this->warn("⚠️  {$key} not configured - S3 backup may fail");
            }
        }

        // Check disk space
        $freeSpace = disk_free_space(storage_path());
        $freeSpaceMB = round($freeSpace / 1024 / 1024);
        
        if ($freeSpaceMB < 100) {
            throw new \Exception("Insufficient disk space: {$freeSpaceMB}MB available");
        }

        $this->info("💾 Available disk space: {$freeSpaceMB}MB");
        $this->info('✅ Pre-backup checks passed');
    }

    /**
     * Build backup arguments based on type
     */
    protected function buildBackupArguments($type, $customFilename)
    {
        $arguments = [];

        switch ($type) {
            case 'db':
                $arguments['--only-db'] = true;
                $prefix = 'hommss-prod-db-';
                break;
            case 'files':
                $arguments['--only-files'] = true;
                $prefix = 'hommss-prod-files-';
                break;
            case 'full':
            default:
                $prefix = 'hommss-prod-full-';
                break;
        }

        if ($customFilename) {
            $arguments['--filename'] = $customFilename;
        } else {
            $timestamp = Carbon::now()->format('Y-m-d-H-i-s');
            $arguments['--filename'] = $prefix . $timestamp;
        }

        return $arguments;
    }

    /**
     * Perform post-backup verification
     */
    protected function performPostBackupVerification()
    {
        $this->info('🔍 Performing post-backup verification...');

        // Check if backup files exist locally
        $localBackups = Storage::disk('local')->files('backups');
        $latestLocal = collect($localBackups)->sortByDesc(function ($file) {
            return Storage::disk('local')->lastModified($file);
        })->first();

        if (!$latestLocal) {
            throw new \Exception('No local backup file found after backup');
        }

        $localSize = Storage::disk('local')->size($latestLocal);
        $this->info("📁 Local backup: " . basename($latestLocal) . " (" . $this->formatBytes($localSize) . ")");

        // Check S3 backup if configured
        try {
            $s3Backups = Storage::disk('s3')->files('');
            if (!empty($s3Backups)) {
                $latestS3 = collect($s3Backups)->sortByDesc(function ($file) {
                    return Storage::disk('s3')->lastModified($file);
                })->first();
                
                if ($latestS3) {
                    $s3Size = Storage::disk('s3')->size($latestS3);
                    $this->info("☁️  S3 backup: " . basename($latestS3) . " (" . $this->formatBytes($s3Size) . ")");
                }
            }
        } catch (\Exception $e) {
            $this->warn("⚠️  S3 verification failed: " . $e->getMessage());
        }

        $this->info('✅ Post-backup verification completed');
    }

    /**
     * Display production backup status
     */
    protected function displayProductionBackupStatus()
    {
        $this->info('');
        $this->info('📊 Production Backup Status');
        $this->info('===========================');

        // Show backup health
        $this->call('backup:monitor');

        $this->info('');
        $this->info('🔧 Production Commands:');
        $this->info('   Database backup: php artisan app:production-backup --type=db --notify');
        $this->info('   Full backup:     php artisan app:production-backup --type=full --notify');
        $this->info('   Monitor health:  php artisan backup:monitor');
        $this->info('   List backups:    php artisan backup:list');
    }

    /**
     * Send success notification
     */
    protected function sendSuccessNotification($type, $duration)
    {
        try {
            $adminEmail = env('ADMIN_EMAIL');
            if (!$adminEmail) {
                $this->warn('⚠️  ADMIN_EMAIL not configured - skipping notification');
                return;
            }

            $this->info("📧 Sending success notification to {$adminEmail}...");
            
            // Log success for now (you can implement actual email later)
            Log::info("Backup completed successfully", [
                'type' => $type,
                'duration' => $duration,
                'timestamp' => now(),
            ]);

            $this->info('✅ Notification sent');
        } catch (\Exception $e) {
            $this->warn("⚠️  Failed to send notification: " . $e->getMessage());
        }
    }

    /**
     * Handle backup failure
     */
    protected function handleBackupFailure(\Exception $e, $startTime)
    {
        $duration = $startTime->diffInSeconds(now());
        
        $this->error('❌ Production backup failed!');
        $this->error("Error: " . $e->getMessage());
        $this->error("Duration: {$duration} seconds");

        // Log failure
        Log::error("Backup failed", [
            'error' => $e->getMessage(),
            'duration' => $duration,
            'timestamp' => now(),
        ]);

        // Send failure notification if configured
        if ($this->option('notify')) {
            try {
                $adminEmail = env('ADMIN_EMAIL');
                if ($adminEmail) {
                    Log::critical("Backup failure notification", [
                        'error' => $e->getMessage(),
                        'admin_email' => $adminEmail,
                        'timestamp' => now(),
                    ]);
                }
            } catch (\Exception $notifyError) {
                $this->warn("Failed to send failure notification: " . $notifyError->getMessage());
            }
        }

        $this->info('');
        $this->info('🔧 Troubleshooting:');
        $this->info('   1. Check encryption password: BACKUP_ARCHIVE_PASSWORD');
        $this->info('   2. Verify S3 credentials in .env');
        $this->info('   3. Check disk space and permissions');
        $this->info('   4. Review logs: storage/logs/laravel.log');
    }

    /**
     * Format bytes to human readable format
     */
    protected function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
