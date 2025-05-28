<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;
use Carbon\Carbon;

class SimpleRestore extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:simple-restore 
                            {backup? : The backup file name to restore}
                            {--list : List available backups}
                            {--latest : Restore the latest backup}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Restore database from simple backup';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('list')) {
            return $this->listBackups();
        }

        $this->info('HOMMSS Simple Database Restore');
        $this->info('==============================');

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
        $backups = $this->getAvailableBackups();
        
        if ($backups->isEmpty()) {
            $this->error('No backups found.');
            return null;
        }

        return $backups->first()['file'];
    }

    /**
     * Find backup file by name
     */
    protected function findBackupFile($name)
    {
        $backups = $this->getAvailableBackups();
        
        $backup = $backups->first(function ($backup) use ($name) {
            return str_contains($backup['file'], $name);
        });

        if (!$backup) {
            $this->error("Backup file containing '{$name}' not found.");
            return null;
        }

        return $backup['file'];
    }

    /**
     * Select backup interactively
     */
    protected function selectBackupInteractively()
    {
        $backups = $this->getAvailableBackups();
        
        if ($backups->isEmpty()) {
            $this->error('No backups found.');
            return null;
        }

        $this->info('Available backups:');
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
    protected function getAvailableBackups()
    {
        return collect(Storage::disk('local')->files('backups'))
            ->filter(function ($file) {
                return pathinfo($file, PATHINFO_EXTENSION) === 'sql';
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
     * List available backups
     */
    protected function listBackups()
    {
        $backups = $this->getAvailableBackups();

        if ($backups->isEmpty()) {
            $this->info('No backups found.');
            return Command::SUCCESS;
        }

        $this->info('Available Simple Backups:');
        $this->info('=========================');

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
        
        // Read backup content
        $backupContent = Storage::disk('local')->get($backupFile);
        
        if (!$backupContent) {
            throw new \Exception('Could not read backup file');
        }

        $this->info('Executing SQL statements...');
        
        // Split SQL statements and execute them
        $statements = explode(';', $backupContent);
        $executedCount = 0;
        
        DB::beginTransaction();
        
        try {
            foreach ($statements as $statement) {
                $statement = trim($statement);
                
                if (empty($statement) || str_starts_with($statement, '--')) {
                    continue;
                }
                
                DB::unprepared($statement);
                $executedCount++;
            }
            
            DB::commit();
            $this->info("Executed {$executedCount} SQL statements successfully");
            
        } catch (\Exception $e) {
            DB::rollback();
            throw new \Exception('SQL execution failed: ' . $e->getMessage());
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
