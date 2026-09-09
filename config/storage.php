<?php

return [
    'max_upload_mb' => (int) env('STORAGE_MAX_UPLOAD_MB', 50),
    'private_url_max_seconds' => (int) env('PRIVATE_URL_MAX_SECONDS', 86400),
    'allowed_mimes' => [
        'jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'txt', 'csv',
        'doc', 'docx', 'xls', 'xlsx', 'zip', 'mp4', 'mp3',
    ],
];



