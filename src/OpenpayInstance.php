<?php

namespace Perafan\CashierOpenpay;

use Illuminate\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Openpay;
use OpenpayApi;

class OpenpayInstance
{
    protected static $openpayInstance;

    /**
     * @return Repository|Application|mixed
     */
    public static function getOpenpayKey()
    {
        return config('cashier_openpay.private_key');
    }

    /**
     * @return Repository|Application|mixed
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
