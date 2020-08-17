<?php

namespace Perafan\CashierOpenpay\Openpay;

use OpenpayCustomer;

class Charge extends Base
{
    /**
     * @param $charge_id
     * @param array $data
     * @param OpenpayCustomer $customer
     * @return mixed
     */
    public static function refund($charge_id, array $data = [], OpenpayCustomer $customer = null)
    {
        $charge = self::find($charge_id, $customer);

        return $charge->refund($data);
    }
}
