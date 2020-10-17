<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Production mode
    |--------------------------------------------------------------------------
    |
    | After approval from the openpay team, you can change this to true and
    | change the testing API keys from the production API keys.
    |
    */

    'production_mode' => env('OPENPAY_PRODUCTION_MODE', false),

    /*
    |--------------------------------------------------------------------------
    | Openpay API Keys
    |--------------------------------------------------------------------------
    |
    | You can retrieve your Openpay API keys from the Openpay control panel.
    |
    */

    'id' => env('OPENPAY_ID', ''),

    'private_key' => env('OPENPAY_PRIVATE_KEY', ''),

    'public_key' => env('OPENPAY_PUBLIC_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Log Errors
    |--------------------------------------------------------------------------
    |
    | Set as true if you want to see the data from Openpay exceptions (HTTP
    | requests) in your laravel.log
    | Note: You need to use OpenpayExceptionsHandler
    */

    'log_errors' => env('OPENPAY_LOG_ERRORS', false),

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

    'model' => env('OPENPAY_MODEL', App\Models\User::class),

    /*
    |--------------------------------------------------------------------------
    | Webhook
    |--------------------------------------------------------------------------
    |
    | Cashier Openpay provides a WebhookController with the methods to add your
    | own business logic.
    | Note: First you need to publish the tag "cashier-openpay-webhook-controller"
    |
    | You may change the url of the webhook, the route name, the controller path
    | or maybe the method.
    |
    */

    'webhook' => [

        'route_name' => env('OPENPAY_WEBHOOK_ROUTE', 'openpay.webhooks.handle'),

        'url' => env('OPENPAY_WEBHOOK_URL', 'openpay/webhooks/handle'),

        'controller' => env('OPENPAY_WEBHOOK_CONTROLLER', '\App\Http\Controllers\WebhookController'),

        'method' => env('OPENPAY_WEBHOOK_METHOD', 'handleWebhook'),
    ],
];
