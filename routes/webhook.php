<?php

Route::name('openpay.webhooks.handle')->post('openpay/webhooks/handle', '\App\Http\Controllers\WebhookController@handleWebhook');
