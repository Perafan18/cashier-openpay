<?php

namespace Perafan\CashierOpenpay\Openpay;

use Illuminate\Support\Str;
use OpenpayCustomer;
use Perafan\CashierOpenpay\OpenpayInstance;

abstract class Base
{
    /**
     * @param $id
     * @param OpenpayCustomer $customer
     * @return mixed
     */
    public static function find($id, OpenpayCustomer $customer = null)
    {
        if (is_null($customer)) {
            return OpenpayInstance::getInstance()->{self::resource()}->get($id);
        }

        return $customer->{self::resource()}->get($id);
    }

    /**
     * @param array $options
     * @param OpenpayCustomer|null $customer
     * @return mixed
     */
    public static function create(array $options = [], OpenpayCustomer $customer = null)
    {
        if (is_null($customer)) {
            return OpenpayInstance::getInstance()->{self::resource()}->create($options);
        }

        return $customer->{self::resource()}->create($options);
    }

    /**
     * @param array $options
     * @param OpenpayCustomer|null $customer
     * @return mixed
     */
    public static function add(array $options = [], OpenpayCustomer $customer = null)
    {
        if (is_null($customer)) {
            return OpenpayInstance::getInstance()->{self::resource()}->add($options);
        }

        return $customer->{self::resource()}->add($options);
    }

    /**
     * @param $id
     * @param OpenpayCustomer|null $customer
     * @return mixed
     */
    public static function delete($id, OpenpayCustomer $customer = null)
    {
        $resource_object = self::find($id, $customer);

        return $resource_object->delete();
    }

    /**
     * @param array $options
     * @param OpenpayCustomer $customer
     * @return mixed
     */
    public static function all(array $options = [], OpenpayCustomer $customer = null)
    {
        if (is_null($customer)) {
            return OpenpayInstance::getInstance()->{self::resource()}->getList($options);
        }
        return $customer->{self::resource()}->getList($options);
    }

    /**
     * @return string
     */
    protected static function resource()
    {
        $klass = preg_replace('/Perafan|CashierOpenpay|Openpay|\\\\/', '', get_called_class());
        $klass = Str::lower($klass);
        return Str::plural($klass);
    }
}
