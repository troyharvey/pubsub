<?php

return [
    'driver' => env('PUBSUB_DRIVER', 'google'),

    'amazon' => [
        'accessKeyId' => env('PUBSUB_AWS_ACCESS_KEY_ID'),
        'secretAccessKey' => env('PUBSUB_AWS_SECRET_ACCESS_KEY'),
    ],

    'google' => [
        'project' => env('GOOGLE_PUBSUB_PROJECT_ID', 'your-google-cloud-project-id'),
    ]
];
