<?php

return [
    'driver' => env('PUBSUB_DRIVER', 'google'),
    'project' => env('GOOGLE_PUBSUB_PROJECT_ID', 'your-google-cloud-project-id'),
];
