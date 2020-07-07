<?php
namespace Perafan\CashierOpenpay;

class Customer
{
    protected static $openpay;

    public static function __callStatic($method, $args)
    {
        if (in_array($method, get_class_methods(static::class))) {
            static::$openpay = OpenpayInstance::getInstance();
            return call_user_func_array([static::class, $method], $args);
        }
    }

    protected static function find($id)
    {
        return static::$openpay->customers->get($id);
    }

    protected static function create(array $options = [])
    {
        return static::$openpay->customers->add($options);
    }

    protected static function delete($id)
    {
        $customer = static::$openpay->customers->get($id);
        return $customer->delete();
    }

    protected static function all(array $options = [])
    {
        return static::$openpay->customers->getList($options);
    }
}
