<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Crypt;
use Carbon\Carbon;
use ZipArchive;

class S3RestoreCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:s3-restore
                            {--backup= : S3 path to backup file}
                            {--list : List available backups}
                            {--interactive : Interactive mode to select backup}
                            {--type=full : Type of restore (database, files, full)}
                            {--password= : Password for encrypted backups}
                            {--force : Force restore without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Restore backup from AWS S3';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔄 Starting AWS S3 restore process...');

        try {
            // Validate S3 configuration
            if (!$this->validateS3Configuration()) {
                return Command::FAILURE;
            }

            // List backups if requested
            if ($this->option('list')) {
                return $this->listBackups();
            }

            // Interactive mode
            if ($this->option('interactive')) {
                $backupPath = $this->selectBackupInteractively();
                if (!$backupPath) {
                    $this->info('Restore cancelled.');
                    return Command::SUCCESS;
                }
            } else {
                $backupPath = $this->option('backup');
                if (!$backupPath) {
                    $this->error('❌ Please specify backup path with --backup option or use --interactive');
                    return Command::FAILURE;
                }
            }

            // Confirm restore
            if (!$this->option('force')) {
                if (!$this->confirm('⚠️ This will overwrite existing data. Continue?')) {
                    $this->info('Restore cancelled.');
                    return Command::SUCCESS;
                }
            }

            // Download and restore
            $localPath = $this->downloadFromS3($backupPath);
            if (!$localPath) {
                return Command::FAILURE;
            }

            $success = $this->performRestore($localPath);

            // Clean up
            if (File::exists($localPath)) {
                unlink($localPath);
            }

            if ($success) {
                $this->info('✅ Restore completed successfully!');
                
                // Log restore
                \Log::info('S3 Restore completed successfully', [
                    'backup_path' => $backupPath,
                    'type' => $this->option('type'),
                    'timestamp' => now()
                ]);

                return Command::SUCCESS;
            } else {
                $this->error('❌ Restore failed');
                return Command::FAILURE;
            }

        } catch (\Exception $e) {
            $this->error('❌ Restore failed: ' . $e->getMessage());
            \Log::error('S3 Restore failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return Command::FAILURE;
        }
    }

    /**
     * Validate S3 configuration
     */
    private function validateS3Configuration(): bool
    {
        $required = ['AWS_ACCESS_KEY_ID', 'AWS_SECRET_ACCESS_KEY', 'AWS_DEFAULT_REGION', 'AWS_BUCKET'];
        
        foreach ($required as $key) {
            if (empty(env($key))) {
                $this->error("❌ Missing required environment variable: {$key}");
                return false;
            }
        }

        try {
            Storage::disk('s3')->exists('test-connection');
            $this->info('✅ S3 connection validated');
            return true;
        } catch (\Exception $e) {
            $this->error('❌ S3 connection failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * List available backups
     */
    private function listBackups(): int
    {
        $this->info('📋 Available backups on S3:');

        try {
            $files = Storage::disk('s3')->allFiles('backups');
            
            if (empty($files)) {
                $this->info('No backups found on S3.');
                return Command::SUCCESS;
            }

            $this->table(
                ['#', 'File', 'Size', 'Modified'],
                collect($files)->map(function ($file, $index) {
                    $size = Storage::disk('s3')->size($file);
                    $modified = Storage::disk('s3')->lastModified($file);
                    
                    return [
                        $index + 1,
                        $file,
                        $this->formatBytes($size),
                        Carbon::createFromTimestamp($modified)->format('Y-m-d H:i:s')
                    ];
                })->toArray()
            );

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Failed to list backups: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Select backup interactively
     */
    private function selectBackupInteractively(): ?string
    {
        try {
            $files = Storage::disk('s3')->allFiles('backups');
            
            if (empty($files)) {
                $this->info('No backups found on S3.');
                return null;
            }

            $choices = [];
            foreach ($files as $index => $file) {
                $size = Storage::disk('s3')->size($file);
                $modified = Storage::disk('s3')->lastModified($file);
                $choices[] = sprintf(
                    '%s (%s, %s)',
                    $file,
                    $this->formatBytes($size),
                    Carbon::createFromTimestamp($modified)->format('Y-m-d H:i:s')
                );
            }

            $selected = $this->choice('Select backup to restore:', $choices);
            $selectedIndex = array_search($selected, $choices);
            
            return $files[$selectedIndex];
        } catch (\Exception $e) {
            $this->error('❌ Failed to select backup: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Download backup from S3
     */
    private function downloadFromS3(string $s3Path): ?string
    {
        $this->info("☁️ Downloading backup from S3: {$s3Path}");

        try {
            $tempDir = storage_path('app/backup-temp');
            if (!File::exists($tempDir)) {
                File::makeDirectory($tempDir, 0755, true);
            }

            $localPath = $tempDir . '/' . basename($s3Path);
            $content = Storage::disk('s3')->get($s3Path);
            
            if ($content === false) {
                $this->error('❌ Failed to download backup from S3');
                return null;
            }

            File::put($localPath, $content);
            $this->info('✅ Backup downloaded successfully');
            
            return $localPath;
        } catch (\Exception $e) {
            $this->error('❌ Download failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Perform the restore operation
     */
    private function performRestore(string $backupPath): bool
    {
        $type = $this->option('type');
        
        // Check if backup is encrypted
        if (str_ends_with($backupPath, '.enc')) {
            $backupPath = $this->decryptBackup($backupPath);
            if (!$backupPath) {
                return false;
            }
        }

        switch ($type) {
            case 'database':
                return $this->restoreDatabase($backupPath);
            case 'files':
                return $this->restoreFiles($backupPath);
            case 'full':
            default:
                return $this->restoreFullBackup($backupPath);
        }
    }

    /**
     * Decrypt backup file
     */
    private function decryptBackup(string $encryptedPath): ?string
    {
        $this->info('🔓 Decrypting backup...');

        try {
            $password = $this->option('password') ?: env('BACKUP_PASSWORD', 'default-password');
            $decryptedPath = str_replace('.enc', '', $encryptedPath);

            $encryptedData = File::get($encryptedPath);
            $decryptedData = Crypt::decrypt($encryptedData);
            File::put($decryptedPath, $decryptedData);

            // Remove encrypted file
            unlink($encryptedPath);

            $this->info('✅ Backup decrypted');
            return $decryptedPath;
        } catch (\Exception $e) {
            $this->error('❌ Decryption failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Restore database
     */
    private function restoreDatabase(string $backupPath): bool
    {
        $this->info('📊 Restoring database...');

        try {
            $zip = new ZipArchive();
            if ($zip->open($backupPath) === TRUE) {
                $tempDir = dirname($backupPath);
                $zip->extractTo($tempDir);
                $zip->close();

                // Find SQL file
                $sqlFile = $tempDir . '/' . pathinfo($backupPath, PATHINFO_FILENAME) . '.sql';
                if (!File::exists($sqlFile)) {
                    // Look for any SQL file
                    $sqlFiles = glob($tempDir . '/*.sql');
                    if (empty($sqlFiles)) {
                        $this->error('❌ No SQL file found in backup');
                        return false;
                    }
                    $sqlFile = $sqlFiles[0];
                }

                // Restore database
                $connection = config('database.default');
                $config = config("database.connections.{$connection}");

                $command = sprintf(
                    'mysql --user="%s" --password="%s" --host="%s" --port="%s" "%s" < "%s"',
                    $config['username'],
                    $config['password'],
                    $config['host'],
                    $config['port'],
                    $config['database'],
                    $sqlFile
                );

                exec($command, $output, $returnVar);

                // Clean up SQL file
                unlink($sqlFile);

                if ($returnVar === 0) {
                    $this->info('✅ Database restored successfully');
                    return true;
                } else {
                    $this->error('❌ Database restore failed');
                    return false;
                }
            }

            return false;
        } catch (\Exception $e) {
            $this->error('❌ Database restore failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Restore files
     */
    private function restoreFiles(string $backupPath): bool
    {
        $this->info('📁 Restoring files...');

        try {
            $zip = new ZipArchive();
            if ($zip->open($backupPath) === TRUE) {
                $zip->extractTo(base_path());
                $zip->close();
                
                $this->info('✅ Files restored successfully');
                return true;
            }

            return false;
        } catch (\Exception $e) {
            $this->error('❌ Files restore failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Restore full backup
     */
    private function restoreFullBackup(string $backupPath): bool
    {
        $this->info('🔄 Restoring full backup...');

        try {
            $zip = new ZipArchive();
            if ($zip->open($backupPath) === TRUE) {
                $tempDir = dirname($backupPath);
                $zip->extractTo($tempDir);
                $zip->close();

                // Restore database
                $dbBackup = $tempDir . '/database.zip';
                if (File::exists($dbBackup)) {
                    if (!$this->restoreDatabase($dbBackup)) {
                        return false;
                    }
                    unlink($dbBackup);
                }

                // Restore files
                $filesBackup = $tempDir . '/files.zip';
                if (File::exists($filesBackup)) {
                    if (!$this->restoreFiles($filesBackup)) {
                        return false;
                    }
                    unlink($filesBackup);
                }

                $this->info('✅ Full backup restored successfully');
                return true;
            }

            return false;
        } catch (\Exception $e) {
            $this->error('❌ Full backup restore failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
