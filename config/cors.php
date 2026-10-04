<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Only the public website may read the API. Extra origins (e.g. a local
    | copy of the website) can be added as a comma separated list in
    | CORS_EXTRA_ORIGINS.
    |
    */

    'paths' => ['api/*'],

    'allowed_methods' => ['GET'],

    'allowed_origins' => array_values(array_filter(array_merge(
        [
            'https://newyorkdrivingschool.academy',
            'https://www.newyorkdrivingschool.academy',
        ],
        array_map('trim', explode(',', (string) env('CORS_EXTRA_ORIGINS', '')))
    ))),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 3600,

    'supports_credentials' => false,

];
