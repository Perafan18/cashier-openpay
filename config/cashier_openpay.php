<?php

return [

    'production_mode' => env('OPENPAY_PRODUCTION_MODE', false),

    'id' => env('OPENPAY_ID', ''),

    'private_key' => env('OPENPAY_PRIVATE_KEY', ''),

    'public_key' => env('OPENPAY_PUBLIC_KEY', ''),

    'log_errors' => env('OPENPAY_LOG_ERRORS', true),
];
