<?php

return [
    /*
     |--------------------------------------------------------------------------
     | Project Constants
     |--------------------------------------------------------------------------
     | Define global, cacheable constants for the project here.
     | Access via: config('constants.key')
     */

    'app' => [
        'default_per_page' => 15,
        'max_per_page' => 100,
    ],

    'pagination' => [
        'default_page' => 1,
    ],

    'uploads' => [
        'avatar_disk' => 'public',
        'avatar_dir' => 'avatars',
        'max_avatar_kb' => 2048,
        'allowed_avatar_mimes' => ['jpeg', 'png', 'jpg', 'gif'],
    ],

    'files' => [
        'default_disk' => env('FILE_DEFAULT_DISK', 'private'),
        'max_size_kb' => (int) env('FILE_MAX_SIZE_KB', 10240), // 10MB
        'max_file_count' => (int) env('FILE_MAX_FILE_COUNT', 10),
        'allowed_mimes' => array_filter(array_map('trim', explode(',', (string) env(
            'FILE_ALLOWED_MIMES',
            'jpg,jpeg,png,gif,webp,pdf,doc,docx,xls,xlsx,csv,txt,zip'
        )))),
    ],

    'cache' => [
        'prefix' => env('CACHE_PREFIX', 'laravel_cache'),
    ],
];
