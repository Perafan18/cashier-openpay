<?php

namespace Perafan\CashierOpenpay;

class Customer
{
    /**
     * @param $id
     * @return mixed
     */
    public static function find($id)
    {
        return OpenpayInstance::getInstance()->customers->get($id);
    }

    /**
     * @param array $options
     * @return mixed
     */
    public static function create(array $options = [])
    {
        return OpenpayInstance::getInstance()->customers->add($options);
    }

    /**
     * @param $id
     * @return mixed
     */
    public static function delete($id)
    {
        $customer = self::find($id);

        return $customer->delete();
    }

    /**
     * @param array $options
     * @return mixed
     */
    public static function all(array $options = [])
    {
        return OpenpayInstance::getInstance()->customers->getList($options);
    }
}
