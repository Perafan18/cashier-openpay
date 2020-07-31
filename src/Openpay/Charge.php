<?php

namespace Perafan\CashierOpenpay\Openpay;

class Charge extends Base
{
    /**
     * @param $charge_id
     * @param array $data
     * @param Customer|null $customer
     * @return mixed
     */
    public static function refund($charge_id, array $data, Customer $customer = null)
    {
        $charge = self::find($charge_id, $customer);

        return $charge->refund($data);
    }
}
