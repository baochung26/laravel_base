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

    'cache' => [
        'prefix' => env('CACHE_PREFIX', 'laravel_cache'),
    ],
];
