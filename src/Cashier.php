<?php

namespace Perafan\CashierOpenpay;

class Cashier
{
    /**
     * Get the webhook route name.
     *
     * @return string
     */
    public static function webhookRouteName()
    {
        return config('cashier_openpay.webhook.route_name');
    }
    /**
     * Get the webhook relative url.
     *
     * @return string
     */
    public static function webhookUrl()
    {
        return config('cashier_openpay.webhook.url');
    }

    /**
     * Get the webhook relative url.
     *
     * @return string
     */
    public static function webhookController()
    {
        return config('cashier_openpay.webhook.controller');
    }

    /**
     * Get the webhook relative url.
     *
     * @return string
     */
    public static function webhookMethod()
    {
        return config('cashier_openpay.webhook.method');
    }
}
