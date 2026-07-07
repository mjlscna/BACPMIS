<?php

return [

    /*
    |--------------------------------------------------------------------------
    | JWT Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for JWT token management in the application.
    | These values control token validity and refresh behavior.
    |
    */

    'secret' => env('JWT_SECRET', env('APP_KEY')),

    'ttl' => env('JWT_TTL', 300), // Token validity in seconds (default: 5 minutes to match API)

    'refresh_threshold' => env('JWT_REFRESH_THRESHOLD', 240), // Refresh after N seconds (default: 4 minutes - before expiry)

    /*
    |--------------------------------------------------------------------------
    | External API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for external API integration
    |
    */

    'api' => [
        'base_url' => env('API_BASE_URL'),
        'timeout' => env('API_TIMEOUT', 10),
    ],

];
