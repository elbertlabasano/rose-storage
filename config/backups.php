<?php

return [
    'enabled' => (bool) env('BACKUP_ENABLED', true),
    'disk' => 'backup',
    'password' => env('BACKUP_ZIP_PASSWORD'),
    'admin_user_ids' => array_values(array_filter(array_map('intval', explode(',', env('BACKUP_ADMIN_USER_IDS', ''))))),
    'google_drive_folder_id' => env('BACKUP_GOOGLE_DRIVE_FOLDER_ID'),
    'mysql_dump_binary' => env('BACKUP_MYSQL_DUMP_BINARY', 'mysqldump'),
    'timeout' => (int) env('BACKUP_TIMEOUT', 300),
    'temp_directory' => storage_path('framework/backups/sql'),
    'limit' => (int) env('BACKUP_BATCH_SIZE', 0),
];
