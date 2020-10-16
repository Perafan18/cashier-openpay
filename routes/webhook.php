<?php

use Illuminate\Support\Facades\Route;
use Perafan\CashierOpenpay\Cashier;

Route::name(Cashier::webhookRouteName())->post(
    Cashier::webhookUrl(),
    Cashier::webhookController() . '@' . Cashier::webhookMethod()
);
