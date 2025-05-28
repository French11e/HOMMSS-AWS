<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;
use Carbon\Carbon;

class WorkingRestore extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:working-restore 
                            {backup? : The backup file name to restore}
                            {--list : List available backups}
                            {--s3 : List S3 backups}
                            {--latest : Restore the latest backup}
                            {--from-s3 : Download and restore from S3}
                            {--decrypt : Decrypt backup before restore}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Restore database from local or S3 backups with decryption support';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('list')) {
            return $this->listBackups();
        }

        if ($this->option('s3')) {
            return $this->listS3Backups();
        }

        $this->info('HOMMSS Working Restore System');
        $this->info('=============================');

        try {
            $backupFile = $this->getBackupFile();
            
            if (!$backupFile) {
                $this->error('No backup file selected.');
                return Command::FAILURE;
            }

            $this->info("Selected backup: {$backupFile}");

            // Confirm restore operation
            if (!$this->confirmRestore()) {
                $this->info('Restore operation cancelled.');
                return Command::SUCCESS;
            }

            // Perform restore
            $this->performRestore($backupFile);

            $this->info('Database restored successfully!');
            $this->info('Running post-restore commands...');
            
            // Clear caches
            Artisan::call('config:clear');
            Artisan::call('cache:clear');
            
            $this->info('Restore completed successfully!');

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Restore failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Get the backup file to restore
     */
    protected function getBackupFile()
    {
        $backupName = $this->argument('backup');
        
        if ($this->option('latest')) {
            return $this->getLatestBackup();
        }

        if ($backupName) {
            return $this->findBackupFile($backupName);
        }

        return $this->selectBackupInteractively();
    }

    /**
     * Get the latest backup file
     */
    protected function getLatestBackup()
    {
        $source = $this->option('from-s3') ? 's3' : 'local';
        $backups = $this->getAvailableBackups($source);
        
        if ($backups->isEmpty()) {
            $this->error("No backups found in {$source}.");
            return null;
        }

        return $backups->first()['file'];
    }

    /**
     * Find backup file by name
     */
    protected function findBackupFile($name)
    {
        $source = $this->option('from-s3') ? 's3' : 'local';
        $backups = $this->getAvailableBackups($source);
        
        $backup = $backups->first(function ($backup) use ($name) {
            return str_contains($backup['file'], $name);
        });

        if (!$backup) {
            $this->error("Backup file containing '{$name}' not found in {$source}.");
            return null;
        }

        return $backup['file'];
    }

    /**
     * Select backup interactively
     */
    protected function selectBackupInteractively()
    {
        $source = $this->option('from-s3') ? 's3' : 'local';
        $backups = $this->getAvailableBackups($source);
        
        if ($backups->isEmpty()) {
            $this->error("No backups found in {$source}.");
            return null;
        }

        $this->info("Available backups from {$source}:");
        $choices = [];
        
        foreach ($backups->take(10) as $index => $backup) {
            $size = $this->formatBytes($backup['size']);
            $date = Carbon::createFromTimestamp($backup['date'])->format('Y-m-d H:i:s');
            $name = basename($backup['file']);
            
            $choices[] = "{$name} ({$size}) - {$date}";
            $this->info(($index + 1) . ". {$name} ({$size}) - {$date}");
        }

        $selection = $this->ask('Enter the number of the backup to restore (1-' . count($choices) . ')');
        
        if (!is_numeric($selection) || $selection < 1 || $selection > count($choices)) {
            $this->error('Invalid selection.');
            return null;
        }

        return $backups->values()[$selection - 1]['file'];
    }

    /**
     * Get available backup files
     */
    protected function getAvailableBackups($source = 'local')
    {
        if ($source === 's3') {
            return $this->getS3Backups();
        }

        return collect(Storage::disk('local')->files('backups'))
            ->filter(function ($file) {
                $ext = pathinfo($file, PATHINFO_EXTENSION);
                return in_array($ext, ['sql', 'zip', 'enc']);
            })
            ->map(function ($file) {
                return [
                    'file' => $file,
                    'size' => Storage::disk('local')->size($file),
                    'date' => Storage::disk('local')->lastModified($file),
                ];
            })
            ->sortByDesc('date');
    }

    /**
     * Get S3 backup files
     */
    protected function getS3Backups()
    {
        try {
            return collect(Storage::disk('s3')->files(''))
                ->filter(function ($file) {
                    $ext = pathinfo($file, PATHINFO_EXTENSION);
                    return in_array($ext, ['sql', 'zip', 'enc']);
                })
                ->map(function ($file) {
                    return [
                        'file' => $file,
                        'size' => Storage::disk('s3')->size($file),
                        'date' => Storage::disk('s3')->lastModified($file),
                    ];
                })
                ->sortByDesc('date');
        } catch (\Exception $e) {
            $this->error('Failed to list S3 backups: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * List available backups
     */
    protected function listBackups()
    {
        $backups = $this->getAvailableBackups('local');

        if ($backups->isEmpty()) {
            $this->info('No local backups found.');
            return Command::SUCCESS;
        }

        $this->info('Available Local Backups:');
        $this->info('========================');

        $this->table(
            ['#', 'Filename', 'Size', 'Date'],
            $backups->take(20)->map(function ($backup, $index) {
                return [
                    $index + 1,
                    basename($backup['file']),
                    $this->formatBytes($backup['size']),
                    Carbon::createFromTimestamp($backup['date'])->format('Y-m-d H:i:s'),
                ];
            })
        );

        return Command::SUCCESS;
    }

    /**
     * List S3 backups
     */
    protected function listS3Backups()
    {
        $backups = $this->getS3Backups();

        if ($backups->isEmpty()) {
            $this->info('No S3 backups found.');
            return Command::SUCCESS;
        }

        $this->info('Available S3 Backups:');
        $this->info('====================');

        $this->table(
            ['#', 'Filename', 'Size', 'Date'],
            $backups->take(20)->map(function ($backup, $index) {
                return [
                    $index + 1,
                    basename($backup['file']),
                    $this->formatBytes($backup['size']),
                    Carbon::createFromTimestamp($backup['date'])->format('Y-m-d H:i:s'),
                ];
            })
        );

        return Command::SUCCESS;
    }

    /**
     * Confirm restore operation
     */
    protected function confirmRestore()
    {
        $this->warn('WARNING: This will replace your current database!');
        $this->warn('Make sure you have a current backup before proceeding.');
        
        return $this->confirm('Are you sure you want to restore from this backup?');
    }

    /**
     * Perform the actual restore
     */
    protected function performRestore($backupFile)
    {
        $this->info('Starting restore process...');
        
        // Download from S3 if needed
        if ($this->option('from-s3')) {
            $backupFile = $this->downloadFromS3($backupFile);
        }

        // Decrypt if needed
        if ($this->option('decrypt') || str_ends_with($backupFile, '.enc')) {
            $backupFile = $this->decryptBackup($backupFile);
        }

        // Restore database
        $this->restoreDatabase($backupFile);
    }

    /**
     * Download backup from S3
     */
    protected function downloadFromS3($s3File)
    {
        $this->info('Downloading backup from S3...');
        
        $localFile = storage_path('app/restore-temp/' . basename($s3File));
        
        // Create temp directory
        $tempDir = dirname($localFile);
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $contents = Storage::disk('s3')->get($s3File);
        file_put_contents($localFile, $contents);

        $this->info('Downloaded: ' . basename($s3File));
        return $localFile;
    }

    /**
     * Decrypt backup file
     */
    protected function decryptBackup($encryptedFile)
    {
        $this->info('Decrypting backup...');

        $config = config('hommss-backup.encryption');
        $password = $config['password'];
        $algorithm = $config['algorithm'];

        $decryptedFile = str_replace('.enc', '', $encryptedFile);
        
        $encryptedData = file_get_contents($encryptedFile);
        $decryptedData = openssl_decrypt($encryptedData, $algorithm, $password, 0, str_repeat('0', 16));

        if ($decryptedData === false) {
            throw new \Exception('Failed to decrypt backup. Check encryption password.');
        }

        file_put_contents($decryptedFile, $decryptedData);
        
        $this->info('Backup decrypted successfully');
        return $decryptedFile;
    }

    /**
     * Restore database from SQL file
     */
    protected function restoreDatabase($sqlFile)
    {
        $this->info('Restoring database...');

        if (!file_exists($sqlFile)) {
            throw new \Exception('SQL file not found: ' . $sqlFile);
        }

        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');

        $command = "mysql -h{$host} -u{$username} -p{$password} {$database} < {$sqlFile}";
        
        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);
        
        if ($returnCode !== 0) {
            throw new \Exception('Database restore failed: ' . implode("\n", $output));
        }

        $this->info('Database restored from: ' . basename($sqlFile));
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
