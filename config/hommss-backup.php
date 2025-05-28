<?php

return [
    /*
     * HOMMSS Custom Backup Configuration
     * This replaces the problematic Spatie backup configuration
     */

    'backup_path' => storage_path('app/backups'),
    
    'encryption' => [
        'enabled' => env('BACKUP_ENCRYPTION_ENABLED', true),
        'password' => env('BACKUP_ARCHIVE_PASSWORD', 'default-password'),
        'algorithm' => 'AES-256-CBC',
    ],

    's3' => [
        'enabled' => env('BACKUP_S3_ENABLED', true),
        'bucket' => env('AWS_BUCKET'),
        'region' => env('AWS_DEFAULT_REGION'),
    ],

    'notifications' => [
        'enabled' => env('BACKUP_NOTIFICATIONS_ENABLED', true),
        'email' => env('ADMIN_EMAIL'),
    ],

    'retention' => [
        'local_days' => 7,
        's3_days' => 30,
    ],

    'database' => [
        'connection' => 'mysql',
        'mysqldump_path' => env('MYSQL_DUMP_BINARY_PATH', '/usr/bin'),
    ],
];
