<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use ZipArchive;

class WorkingBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:working-backup
                            {--type=db : Type of backup (db, files, full)}
                            {--filename= : Custom filename for the backup}
                            {--encrypt : Enable encryption}
                            {--s3 : Upload to S3}
                            {--notify : Send notification}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Working backup system that bypasses Spatie ZIP issues';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $startTime = now();
        $this->info('HOMMSS Working Backup System');
        $this->info('============================');

        try {
            $type = $this->option('type');
            $filename = $this->option('filename');
            $encrypt = $this->option('encrypt');
            $uploadS3 = $this->option('s3');
            $notify = $this->option('notify');

            // Pre-backup checks
            $this->performChecks();

            // Generate filename
            $backupFilename = $this->generateFilename($type, $filename);

            $this->info("Starting {$type} backup...");
            $this->info("Filename: {$backupFilename}");

            if ($encrypt) {
                $this->info('Encryption: AES-256 enabled');
            } else {
                $this->info('Encryption: Disabled');
            }

            // Perform backup based on type
            $backupPath = $this->performBackup($type, $backupFilename);

            // Encrypt if requested
            if ($encrypt) {
                $backupPath = $this->encryptBackup($backupPath);
            }

            // Upload to S3 if requested
            if ($uploadS3) {
                $this->uploadToS3($backupPath);
            }

            // Calculate duration
            $duration = $startTime->diffInSeconds(now());

            $this->info('');
            $this->info('Backup completed successfully!');
            $this->info("Duration: {$duration} seconds");
            $this->info("Local path: {$backupPath}");

            // Send notification if requested
            if ($notify) {
                $this->sendNotification($type, $duration, true);
            }

            $this->displayBackupInfo();

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->handleFailure($e, $startTime);
            return Command::FAILURE;
        }
    }

    /**
     * Perform pre-backup checks
     */
    protected function performChecks()
    {
        $this->info('Performing pre-backup checks...');

        // Check disk space
        $freeSpace = disk_free_space(storage_path());
        $freeSpaceMB = round($freeSpace / 1024 / 1024);

        if ($freeSpaceMB < 100) {
            throw new \Exception("Insufficient disk space: {$freeSpaceMB}MB available");
        }

        $this->info("Available disk space: {$freeSpaceMB}MB");

        // Check database connection
        try {
            DB::connection()->getPdo();
            $this->info('Database connection: OK');
        } catch (\Exception $e) {
            throw new \Exception('Database connection failed: ' . $e->getMessage());
        }

        // Create backup directory
        $backupDir = storage_path('app/backups');
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $this->info('Pre-backup checks passed');
    }

    /**
     * Generate backup filename
     */
    protected function generateFilename($type, $customFilename)
    {
        if ($customFilename) {
            return $customFilename;
        }

        $prefix = match($type) {
            'db' => 'hommss-db-',
            'files' => 'hommss-files-',
            'full' => 'hommss-full-',
            default => 'hommss-backup-'
        };

        return $prefix . Carbon::now()->format('Y-m-d-H-i-s');
    }

    /**
     * Perform backup based on type
     */
    protected function performBackup($type, $filename)
    {
        return match($type) {
            'db' => $this->backupDatabase($filename),
            'files' => $this->backupFiles($filename),
            'full' => $this->backupFull($filename),
            default => $this->backupDatabase($filename)
        };
    }

    /**
     * Backup database only
     */
    protected function backupDatabase($filename)
    {
        $this->info('Creating database backup...');

        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');

        $backupPath = storage_path("app/backups/{$filename}.sql");

        // Use mysqldump
        $command = "mysqldump -h{$host} -u{$username} -p{$password} {$database} > {$backupPath}";

        $result = Process::run($command);

        if (!$result->successful()) {
            throw new \Exception('Database backup failed: ' . $result->errorOutput());
        }

        $size = filesize($backupPath);
        $this->info("Database backup created: " . $this->formatBytes($size));

        return $backupPath;
    }

    /**
     * Backup files only
     */
    protected function backupFiles($filename)
    {
        $this->info('Creating files backup...');

        $backupPath = storage_path("app/backups/{$filename}.zip");
        $zip = new ZipArchive();

        if ($zip->open($backupPath, ZipArchive::CREATE) !== TRUE) {
            throw new \Exception('Cannot create ZIP file');
        }

        // Add important directories
        $this->addDirectoryToZip($zip, app_path(), 'app');
        $this->addDirectoryToZip($zip, config_path(), 'config');
        $this->addDirectoryToZip($zip, resource_path(), 'resources');
        $this->addDirectoryToZip($zip, public_path(), 'public');

        $zip->close();

        $size = filesize($backupPath);
        $this->info("Files backup created: " . $this->formatBytes($size));

        return $backupPath;
    }

    /**
     * Backup database and files
     */
    protected function backupFull($filename)
    {
        $this->info('Creating full backup...');

        // First create database backup
        $dbPath = $this->backupDatabase($filename . '-db');

        // Create ZIP with database and files
        $backupPath = storage_path("app/backups/{$filename}.zip");
        $zip = new ZipArchive();

        if ($zip->open($backupPath, ZipArchive::CREATE) !== TRUE) {
            throw new \Exception('Cannot create ZIP file');
        }

        // Add database backup
        $zip->addFile($dbPath, 'database.sql');

        // Add important directories
        $this->addDirectoryToZip($zip, app_path(), 'app');
        $this->addDirectoryToZip($zip, config_path(), 'config');
        $this->addDirectoryToZip($zip, resource_path(), 'resources');
        $this->addDirectoryToZip($zip, public_path(), 'public');

        $zip->close();

        // Remove temporary database file
        unlink($dbPath);

        $size = filesize($backupPath);
        $this->info("Full backup created: " . $this->formatBytes($size));

        return $backupPath;
    }

    /**
     * Add directory to ZIP
     */
    protected function addDirectoryToZip($zip, $dir, $zipDir)
    {
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = $zipDir . '/' . substr($filePath, strlen($dir) + 1);
                $zip->addFile($filePath, $relativePath);
            }
        }
    }

    /**
     * Encrypt backup file
     */
    protected function encryptBackup($backupPath)
    {
        $this->info('Encrypting backup...');

        $config = config('hommss-backup.encryption');
        $password = $config['password'];
        $algorithm = $config['algorithm'];

        $encryptedPath = $backupPath . '.enc';

        $data = file_get_contents($backupPath);
        $encrypted = openssl_encrypt($data, $algorithm, $password, 0, str_repeat('0', 16));

        file_put_contents($encryptedPath, $encrypted);
        unlink($backupPath);

        $this->info('Backup encrypted with ' . $algorithm);
        return $encryptedPath;
    }

    /**
     * Upload to S3
     */
    protected function uploadToS3($backupPath)
    {
        $this->info('Uploading to S3...');

        try {
            $filename = basename($backupPath);
            $contents = file_get_contents($backupPath);

            Storage::disk('s3')->put($filename, $contents);

            $this->info("Uploaded to S3: {$filename}");
        } catch (\Exception $e) {
            $this->warn("S3 upload failed: " . $e->getMessage());
        }
    }

    /**
     * Send notification
     */
    protected function sendNotification($type, $duration, $success)
    {
        $status = $success ? 'SUCCESS' : 'FAILED';
        $message = "Backup {$status}: {$type} backup completed in {$duration} seconds";

        Log::info($message);
        $this->info("Notification logged: {$message}");
    }

    /**
     * Display backup information
     */
    protected function displayBackupInfo()
    {
        $this->info('');
        $this->info('Available Commands:');
        $this->info('   Database backup: php artisan app:working-backup --type=db --encrypt --s3 --notify');
        $this->info('   Files backup:    php artisan app:working-backup --type=files --encrypt --s3');
        $this->info('   Full backup:     php artisan app:working-backup --type=full --encrypt --s3 --notify');
        $this->info('   Custom filename: php artisan app:working-backup --filename="my-backup" --encrypt');
    }

    /**
     * Handle backup failure
     */
    protected function handleFailure(\Exception $e, $startTime)
    {
        $duration = $startTime->diffInSeconds(now());

        $this->error('Backup failed!');
        $this->error("Error: " . $e->getMessage());
        $this->error("Duration: {$duration} seconds");

        if ($this->option('notify')) {
            $this->sendNotification($this->option('type'), $duration, false);
        }
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
