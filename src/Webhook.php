<?php

namespace Perafan\CashierOpenpay;

class Webhook
{
    public static function find($id)
    {
        return OpenpayInstance::getInstance()->webhooks->get($id);
    }

    public static function create(array $options = [])
    {
        return OpenpayInstance::getInstance()->webhooks->add($options);
    }

    public static function delete($id)
    {
        $webhook = OpenpayInstance::getInstance()->webhooks->get($id);
        return $webhook->delete();
    }

    public static function all(array $options = [])
    {
        return OpenpayInstance::getInstance()->webhooks->getList($options);
    }
}
