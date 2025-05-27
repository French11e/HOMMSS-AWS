<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Crypt;
use Carbon\Carbon;
use ZipArchive;

class S3BackupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:s3-backup
                            {--type=full : Type of backup (database, files, full)}
                            {--filename= : Custom filename for the backup}
                            {--encrypt : Encrypt the backup}
                            {--password= : Password for encrypting the backup}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a backup and upload to AWS S3';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🚀 Starting AWS S3 backup process...');

        try {
            // Validate S3 configuration
            if (!$this->validateS3Configuration()) {
                return Command::FAILURE;
            }

            $type = $this->option('type');
            $customFilename = $this->option('filename');
            $encrypt = $this->option('encrypt');
            $password = $this->option('password');

            // Generate filename
            $timestamp = Carbon::now()->format('Y-m-d-H-i-s');
            $filename = $customFilename ?: "hommss-{$type}-backup-{$timestamp}";

            // Create temporary directory
            $tempDir = storage_path('app/backup-temp');
            if (!File::exists($tempDir)) {
                File::makeDirectory($tempDir, 0755, true);
            }

            $backupPath = null;

            switch ($type) {
                case 'database':
                    $backupPath = $this->createDatabaseBackup($tempDir, $filename);
                    break;
                case 'files':
                    $backupPath = $this->createFilesBackup($tempDir, $filename);
                    break;
                case 'full':
                default:
                    $backupPath = $this->createFullBackup($tempDir, $filename);
                    break;
            }

            if (!$backupPath) {
                $this->error('❌ Failed to create backup');
                return Command::FAILURE;
            }

            // Encrypt if requested
            if ($encrypt) {
                $backupPath = $this->encryptBackup($backupPath, $password);
            }

            // Upload to S3
            $s3Path = $this->uploadToS3($backupPath, $filename);

            if ($s3Path) {
                $this->info("✅ Backup successfully uploaded to S3: {$s3Path}");
                
                // Log backup creation
                \Log::info('S3 Backup created successfully', [
                    'type' => $type,
                    'filename' => $filename,
                    's3_path' => $s3Path,
                    'encrypted' => $encrypt,
                    'size' => File::size($backupPath),
                    'timestamp' => now()
                ]);

                // Clean up temporary files
                $this->cleanupTempFiles($tempDir);

                return Command::SUCCESS;
            } else {
                $this->error('❌ Failed to upload backup to S3');
                return Command::FAILURE;
            }

        } catch (\Exception $e) {
            $this->error('❌ Backup failed: ' . $e->getMessage());
            \Log::error('S3 Backup failed', [
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

        // Test S3 connection
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
     * Create database backup
     */
    private function createDatabaseBackup(string $tempDir, string $filename): ?string
    {
        $this->info('📊 Creating database backup...');

        $connection = config('database.default');
        $config = config("database.connections.{$connection}");

        $sqlFile = "{$tempDir}/{$filename}.sql";

        // Create mysqldump command
        $command = sprintf(
            'mysqldump --user="%s" --password="%s" --host="%s" --port="%s" "%s" > "%s"',
            $config['username'],
            $config['password'],
            $config['host'],
            $config['port'],
            $config['database'],
            $sqlFile
        );

        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            $this->error('❌ Database dump failed');
            return null;
        }

        // Create zip file
        $zipFile = "{$tempDir}/{$filename}.zip";
        $zip = new ZipArchive();
        
        if ($zip->open($zipFile, ZipArchive::CREATE) === TRUE) {
            $zip->addFile($sqlFile, basename($sqlFile));
            $zip->close();
            
            // Remove SQL file
            unlink($sqlFile);
            
            $this->info('✅ Database backup created');
            return $zipFile;
        }

        return null;
    }

    /**
     * Create files backup
     */
    private function createFilesBackup(string $tempDir, string $filename): ?string
    {
        $this->info('📁 Creating files backup...');

        $zipFile = "{$tempDir}/{$filename}.zip";
        $zip = new ZipArchive();

        if ($zip->open($zipFile, ZipArchive::CREATE) === TRUE) {
            // Add important directories
            $this->addDirectoryToZip($zip, storage_path('app/public'), 'storage/public');
            $this->addDirectoryToZip($zip, public_path('uploads'), 'uploads');
            
            $zip->close();
            $this->info('✅ Files backup created');
            return $zipFile;
        }

        return null;
    }

    /**
     * Create full backup (database + files)
     */
    private function createFullBackup(string $tempDir, string $filename): ?string
    {
        $this->info('🔄 Creating full backup...');

        // Create database backup first
        $dbBackup = $this->createDatabaseBackup($tempDir, $filename . '-db');
        if (!$dbBackup) {
            return null;
        }

        // Create files backup
        $filesBackup = $this->createFilesBackup($tempDir, $filename . '-files');
        if (!$filesBackup) {
            return null;
        }

        // Combine into single zip
        $fullBackupFile = "{$tempDir}/{$filename}-full.zip";
        $zip = new ZipArchive();

        if ($zip->open($fullBackupFile, ZipArchive::CREATE) === TRUE) {
            $zip->addFile($dbBackup, 'database.zip');
            $zip->addFile($filesBackup, 'files.zip');
            $zip->close();

            // Clean up individual backups
            unlink($dbBackup);
            unlink($filesBackup);

            $this->info('✅ Full backup created');
            return $fullBackupFile;
        }

        return null;
    }

    /**
     * Add directory to zip recursively
     */
    private function addDirectoryToZip(ZipArchive $zip, string $dir, string $zipPath): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = $zipPath . '/' . substr($filePath, strlen($dir) + 1);
                $zip->addFile($filePath, $relativePath);
            }
        }
    }

    /**
     * Encrypt backup file
     */
    private function encryptBackup(string $backupPath, ?string $password): string
    {
        $this->info('🔐 Encrypting backup...');

        $password = $password ?: env('BACKUP_PASSWORD', 'default-password');
        $encryptedPath = $backupPath . '.enc';

        $data = File::get($backupPath);
        $encrypted = Crypt::encrypt($data);
        File::put($encryptedPath, $encrypted);

        // Remove original file
        unlink($backupPath);

        $this->info('✅ Backup encrypted');
        return $encryptedPath;
    }

    /**
     * Upload backup to S3
     */
    private function uploadToS3(string $backupPath, string $filename): ?string
    {
        $this->info('☁️ Uploading to S3...');

        try {
            $s3Path = 'backups/' . date('Y/m/d') . '/' . basename($backupPath);
            
            $uploaded = Storage::disk('s3')->put(
                $s3Path,
                File::get($backupPath),
                'private'
            );

            if ($uploaded) {
                return $s3Path;
            }

            return null;
        } catch (\Exception $e) {
            $this->error('❌ S3 upload failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Clean up temporary files
     */
    private function cleanupTempFiles(string $tempDir): void
    {
        $this->info('🧹 Cleaning up temporary files...');
        
        $files = glob($tempDir . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
}
