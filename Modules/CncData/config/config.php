<?php

return [
    // Where uploaded programs are kept. Files are stored once per SHA-256.
    'disk' => env('CNC_DATA_DISK', 'local'),
    'path' => env('CNC_DATA_PATH', 'cnc-programs'),

    // Rapid traverse rate (in/min) used for estimates when a machine has none.
    // 600 is the rapid_rate in the shop's ProNest XPR300 machine config.
    'rapid_rate' => (float) env('CNC_RAPID_RATE', 600),

    // JSON endpoints under /api/cnc. The app has no token auth set up yet, so
    // these default to the logged-in web session. For a shop-floor agent that
    // posts files, run `php artisan install:api`, add HasApiTokens to User and
    // switch this to ['api', 'auth:sanctum'].
    'api_middleware' => ['web', 'auth'],

    'calibration' => [
        // Completed runs needed before an estimate counts as validated.
        'min_runs' => 5,
        // Most recent runs used for the factor.
        'window' => 10,
        // Max spread (median absolute deviation / median) of actual÷estimate
        // ratios for the factor to be trusted.
        'max_spread' => 0.15,
    ],

    // A run whose actual time is this far from the estimate gets flagged.
    'variance_alert' => 0.25,
];
