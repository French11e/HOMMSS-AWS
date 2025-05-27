<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class S3BackupManagerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:s3-backup-manager
                            {action : Action to perform (list, cleanup, status, test)}
                            {--days=30 : Number of days to keep backups for cleanup}
                            {--dry-run : Show what would be deleted without actually deleting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage AWS S3 backups (list, cleanup, status, test)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $action = $this->argument('action');

        switch ($action) {
            case 'list':
                return $this->listBackups();
            case 'cleanup':
                return $this->cleanupOldBackups();
            case 'status':
                return $this->showBackupStatus();
            case 'test':
                return $this->testS3Connection();
            default:
                $this->error('❌ Invalid action. Available actions: list, cleanup, status, test');
                return Command::FAILURE;
        }
    }

    /**
     * List all backups on S3
     */
    private function listBackups(): int
    {
        $this->info('📋 Listing all backups on S3...');

        try {
            $files = Storage::disk('s3')->allFiles('backups');
            
            if (empty($files)) {
                $this->info('No backups found on S3.');
                return Command::SUCCESS;
            }

            $totalSize = 0;
            $backups = [];

            foreach ($files as $file) {
                $size = Storage::disk('s3')->size($file);
                $modified = Storage::disk('s3')->lastModified($file);
                $totalSize += $size;

                $backups[] = [
                    'file' => $file,
                    'size' => $this->formatBytes($size),
                    'size_bytes' => $size,
                    'modified' => Carbon::createFromTimestamp($modified)->format('Y-m-d H:i:s'),
                    'age_days' => Carbon::createFromTimestamp($modified)->diffInDays(now())
                ];
            }

            // Sort by modification date (newest first)
            usort($backups, function ($a, $b) {
                return strcmp($b['modified'], $a['modified']);
            });

            $this->table(
                ['File', 'Size', 'Modified', 'Age (days)'],
                array_map(function ($backup) {
                    return [
                        $backup['file'],
                        $backup['size'],
                        $backup['modified'],
                        $backup['age_days']
                    ];
                }, $backups)
            );

            $this->info("📊 Total backups: " . count($backups));
            $this->info("💾 Total size: " . $this->formatBytes($totalSize));

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Failed to list backups: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Clean up old backups
     */
    private function cleanupOldBackups(): int
    {
        $days = (int) $this->option('days');
        $dryRun = $this->option('dry-run');

        $this->info("🧹 Cleaning up backups older than {$days} days...");

        if ($dryRun) {
            $this->warn('🔍 DRY RUN MODE - No files will be deleted');
        }

        try {
            $files = Storage::disk('s3')->allFiles('backups');
            $cutoffDate = Carbon::now()->subDays($days);
            $toDelete = [];
            $totalSize = 0;

            foreach ($files as $file) {
                $modified = Storage::disk('s3')->lastModified($file);
                $fileDate = Carbon::createFromTimestamp($modified);

                if ($fileDate->lt($cutoffDate)) {
                    $size = Storage::disk('s3')->size($file);
                    $toDelete[] = [
                        'file' => $file,
                        'size' => $size,
                        'modified' => $fileDate->format('Y-m-d H:i:s'),
                        'age_days' => $fileDate->diffInDays(now())
                    ];
                    $totalSize += $size;
                }
            }

            if (empty($toDelete)) {
                $this->info('✅ No old backups found to clean up.');
                return Command::SUCCESS;
            }

            $this->table(
                ['File', 'Size', 'Modified', 'Age (days)'],
                array_map(function ($backup) {
                    return [
                        $backup['file'],
                        $this->formatBytes($backup['size']),
                        $backup['modified'],
                        $backup['age_days']
                    ];
                }, $toDelete)
            );

            $this->info("📊 Files to delete: " . count($toDelete));
            $this->info("💾 Space to free: " . $this->formatBytes($totalSize));

            if (!$dryRun) {
                if (!$this->confirm('⚠️ Are you sure you want to delete these backups?')) {
                    $this->info('Cleanup cancelled.');
                    return Command::SUCCESS;
                }

                $deleted = 0;
                foreach ($toDelete as $backup) {
                    try {
                        Storage::disk('s3')->delete($backup['file']);
                        $deleted++;
                        $this->info("🗑️ Deleted: {$backup['file']}");
                    } catch (\Exception $e) {
                        $this->error("❌ Failed to delete {$backup['file']}: " . $e->getMessage());
                    }
                }

                $this->info("✅ Cleanup completed. Deleted {$deleted} files.");
                
                // Log cleanup
                \Log::info('S3 Backup cleanup completed', [
                    'files_deleted' => $deleted,
                    'space_freed' => $totalSize,
                    'retention_days' => $days
                ]);
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Cleanup failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Show backup status and statistics
     */
    private function showBackupStatus(): int
    {
        $this->info('📊 S3 Backup Status Report');
        $this->line('');

        try {
            // S3 Connection Test
            $this->info('🔗 Testing S3 Connection...');
            try {
                Storage::disk('s3')->exists('test-connection');
                $this->info('✅ S3 connection: OK');
            } catch (\Exception $e) {
                $this->error('❌ S3 connection: FAILED - ' . $e->getMessage());
                return Command::FAILURE;
            }

            // Backup Statistics
            $files = Storage::disk('s3')->allFiles('backups');
            
            if (empty($files)) {
                $this->warn('⚠️ No backups found on S3');
                return Command::SUCCESS;
            }

            $totalSize = 0;
            $newestDate = null;
            $oldestDate = null;
            $typeCount = ['database' => 0, 'files' => 0, 'full' => 0];

            foreach ($files as $file) {
                $size = Storage::disk('s3')->size($file);
                $modified = Storage::disk('s3')->lastModified($file);
                $fileDate = Carbon::createFromTimestamp($modified);

                $totalSize += $size;

                if (!$newestDate || $fileDate->gt($newestDate)) {
                    $newestDate = $fileDate;
                }

                if (!$oldestDate || $fileDate->lt($oldestDate)) {
                    $oldestDate = $fileDate;
                }

                // Determine backup type
                if (str_contains($file, 'database')) {
                    $typeCount['database']++;
                } elseif (str_contains($file, 'files')) {
                    $typeCount['files']++;
                } elseif (str_contains($file, 'full')) {
                    $typeCount['full']++;
                }
            }

            // Display statistics
            $this->table(
                ['Metric', 'Value'],
                [
                    ['Total Backups', count($files)],
                    ['Total Size', $this->formatBytes($totalSize)],
                    ['Database Backups', $typeCount['database']],
                    ['Files Backups', $typeCount['files']],
                    ['Full Backups', $typeCount['full']],
                    ['Newest Backup', $newestDate ? $newestDate->format('Y-m-d H:i:s') : 'N/A'],
                    ['Oldest Backup', $oldestDate ? $oldestDate->format('Y-m-d H:i:s') : 'N/A'],
                    ['Last Backup Age', $newestDate ? $newestDate->diffForHumans() : 'N/A'],
                ]
            );

            // Health Check
            $this->line('');
            $this->info('🏥 Health Check:');
            
            if ($newestDate && $newestDate->diffInHours(now()) > 25) {
                $this->warn('⚠️ Last backup is more than 24 hours old');
            } else {
                $this->info('✅ Recent backup found');
            }

            if ($totalSize > 1024 * 1024 * 1024 * 5) { // 5GB
                $this->warn('⚠️ Backup storage is getting large (>5GB)');
            } else {
                $this->info('✅ Storage usage is reasonable');
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Status check failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Test S3 connection and permissions
     */
    private function testS3Connection(): int
    {
        $this->info('🧪 Testing S3 Connection and Permissions...');

        try {
            // Test basic connection
            $this->info('1. Testing basic connection...');
            Storage::disk('s3')->exists('test-connection');
            $this->info('✅ Basic connection: OK');

            // Test write permissions
            $this->info('2. Testing write permissions...');
            $testFile = 'test-files/connection-test-' . time() . '.txt';
            $testContent = 'HOMMSS S3 Connection Test - ' . now();
            Storage::disk('s3')->put($testFile, $testContent);
            $this->info('✅ Write permissions: OK');

            // Test read permissions
            $this->info('3. Testing read permissions...');
            $readContent = Storage::disk('s3')->get($testFile);
            if ($readContent === $testContent) {
                $this->info('✅ Read permissions: OK');
            } else {
                $this->error('❌ Read permissions: FAILED');
                return Command::FAILURE;
            }

            // Test delete permissions
            $this->info('4. Testing delete permissions...');
            Storage::disk('s3')->delete($testFile);
            $this->info('✅ Delete permissions: OK');

            // Test list permissions
            $this->info('5. Testing list permissions...');
            Storage::disk('s3')->allFiles('backups');
            $this->info('✅ List permissions: OK');

            $this->line('');
            $this->info('🎉 All S3 tests passed! Your backup system is ready.');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ S3 test failed: ' . $e->getMessage());
            return Command::FAILURE;
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
