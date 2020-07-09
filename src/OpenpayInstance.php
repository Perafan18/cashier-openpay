<?php

namespace Perafan\CashierOpenpay;

use Openpay;
use OpenpayApi;

class OpenpayInstance
{
    protected static $openpayInstance;

    /**
     * @return \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|mixed
     */
    public static function getOpenpayKey()
    {
        return config('cashier_openpay.private_key');
    }

    /**
     * @return \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|mixed
     */
    public static function getOpenpayId()
    {
        return config('cashier_openpay.id');
    }


    /**
     * @return OpenpayApi
     */
    public static function getInstance()
    {
        if (! static::$openpayInstance) {
            static::$openpayInstance = Openpay::getInstance(static::getOpenpayId(), static::getOpenpayKey());
        }

        return static::$openpayInstance;
    }
}
