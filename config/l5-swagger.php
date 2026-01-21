<?php

return [
    'defaults' => [
        'routes' => [
            'docs' => 'api/documentation',
            'oauth2_callback' => 'api/oauth2-callback',
            'middleware' => [
                'api',
            ],
            'group_middleware' => [],
        ],

        'paths' => [
            'use_absolute_path' => env('L5_SWAGGER_USE_ABSOLUTE_PATH', false),
            'docs_json' => 'api-docs.json',
            'docs_yaml' => 'api-docs.yaml',
            'format_to_use_for_docs' => env('L5_FORMAT_TO_USE_FOR_DOCS', 'json'),
            'annotations' => [
                app_path('Http/Controllers'),
            ],
        ],

        'swagger' => [
            'version' => env('SWAGGER_VERSION', '3.0'),
        ],

        'securityDefinitions' => [
            'sanctum' => [
                'type' => 'apiKey',
                'description' => 'Enter token in format: Bearer {token}',
                'name' => 'Authorization',
                'in' => 'header',
            ],
        ],

        'security' => [
            [
                'sanctum' => [],
            ],
        ],
    ],

    'constants' => [
        'L5_SWAGGER_CONST_HOST' => env('L5_SWAGGER_CONST_HOST', 'http://localhost:8000'),
    ],
];
