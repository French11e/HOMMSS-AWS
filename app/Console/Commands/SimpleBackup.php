<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class SimpleBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:simple-backup 
                            {--filename= : Custom filename for the backup}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a simple database backup using Laravel DB';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('HOMMSS Simple Database Backup');
        $this->info('=============================');

        try {
            $filename = $this->option('filename') ?: 'hommss-backup-' . Carbon::now()->format('Y-m-d-H-i-s');
            
            $this->info('Starting database backup...');
            
            // Get all tables
            $tables = $this->getAllTables();
            $this->info('Found ' . count($tables) . ' tables to backup');
            
            // Create backup content
            $backupContent = $this->createBackupContent($tables);
            
            // Save backup file
            $backupPath = 'backups/' . $filename . '.sql';
            Storage::disk('local')->put($backupPath, $backupContent);
            
            $this->info('Backup saved to: ' . storage_path('app/' . $backupPath));
            $this->info('Backup size: ' . $this->formatBytes(strlen($backupContent)));
            
            // Show backup info
            $this->displayBackupInfo();
            
            $this->info('Backup completed successfully!');
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('Backup failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Get all tables from the database
     */
    protected function getAllTables()
    {
        $database = config('database.connections.mysql.database');
        return DB::select("SHOW TABLES FROM `{$database}`");
    }

    /**
     * Create backup content
     */
    protected function createBackupContent($tables)
    {
        $content = "-- HOMMSS Database Backup\n";
        $content .= "-- Generated on: " . Carbon::now()->format('Y-m-d H:i:s') . "\n";
        $content .= "-- Database: " . config('database.connections.mysql.database') . "\n\n";
        
        $content .= "SET FOREIGN_KEY_CHECKS=0;\n\n";
        
        foreach ($tables as $table) {
            $tableName = array_values((array) $table)[0];
            $this->info("Backing up table: {$tableName}");
            
            // Get table structure
            $createTable = DB::select("SHOW CREATE TABLE `{$tableName}`")[0];
            $content .= "-- Table structure for `{$tableName}`\n";
            $content .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
            $content .= $createTable->{'Create Table'} . ";\n\n";
            
            // Get table data
            $rows = DB::table($tableName)->get();
            
            if ($rows->count() > 0) {
                $content .= "-- Data for table `{$tableName}`\n";
                $content .= "INSERT INTO `{$tableName}` VALUES\n";
                
                $values = [];
                foreach ($rows as $row) {
                    $rowData = [];
                    foreach ((array) $row as $value) {
                        if (is_null($value)) {
                            $rowData[] = 'NULL';
                        } else {
                            $rowData[] = "'" . addslashes($value) . "'";
                        }
                    }
                    $values[] = '(' . implode(',', $rowData) . ')';
                }
                
                $content .= implode(",\n", $values) . ";\n\n";
            }
        }
        
        $content .= "SET FOREIGN_KEY_CHECKS=1;\n";
        
        return $content;
    }

    /**
     * Display backup information
     */
    protected function displayBackupInfo()
    {
        $this->info('');
        $this->info('Backup Information:');
        $this->info('------------------');
        $this->info('Backups are stored in: ' . storage_path('app/backups'));
        $this->info('Backups are NOT encrypted (simple backup)');
        $this->info('');

        // List recent backups
        $this->info('Recent backups:');
        $backups = collect(Storage::disk('local')->files('backups'))
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
            ->sortByDesc('date')
            ->take(5);

        if ($backups->isEmpty()) {
            $this->info('No backups found.');
            return;
        }

        $this->table(
            ['Filename', 'Size', 'Date'],
            $backups->map(function ($backup) {
                return [
                    basename($backup['file']),
                    $this->formatBytes($backup['size']),
                    Carbon::createFromTimestamp($backup['date'])->format('Y-m-d H:i:s'),
                ];
            })
        );
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
