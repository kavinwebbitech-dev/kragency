<?php

declare(strict_types=1);

return [
    'default' => env('FIREBASE_PROJECT', 'app'),

    'projects' => [
        'app' => [
            'credentials' => [
                'file' => env(storage_path('FIREBASE_CREDENTIALS')),
            ],

            'messaging' => [
                'database_url' => env('FIREBASE_DATABASE_URL'),
            ],
        ],
    ],
];

