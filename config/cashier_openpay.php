<?php

return [

    'production_mode' => env('OPENPAY_PRODUCTION_MODE', false),

    'id' => env('OPENPAY_ID', ''),

    'private_key' => env('OPENPAY_PRIVATE_KEY', ''),

    'public_key' => env('OPENPAY_PUBLIC_KEY', ''),

    'log_errors' => env('OPENPAY_LOG_ERRORS', true),

    /*
    |--------------------------------------------------------------------------
    | Cashier Model
    |--------------------------------------------------------------------------
    |
    | This is the model in your application that implements the Billable trait
    | provided by Cashier. It will serve as the primary model you use while
    | interacting with Cashier related methods, subscriptions, and so on.
    |
    */

    'model' => env('OPENPAY_MODEL', App\User::class),
];
