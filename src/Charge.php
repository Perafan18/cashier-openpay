<?php

namespace Perafan\CashierOpenpay;

class Charge
{
    protected static function find($user, $id)
    {
        return $user->charges->get($id);
    }

    protected static function create($user, array $options = [])
    {
        return $user->charges->create($options);
    }

    protected static function all($user, array $options = [])
    {
        return $user->charges->getList($options);
    }
}
