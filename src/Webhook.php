<?php

namespace Perafan\CashierOpenpay;

class Webhook
{
    /**
     * @param $id
     * @return mixed
     */
    public static function find($id)
    {
        return OpenpayInstance::getInstance()->webhooks->get($id);
    }

    /**
     * @param array $options
     * @return mixed
     */
    public static function create(array $options = [])
    {
        return OpenpayInstance::getInstance()->webhooks->add($options);
    }

    /**
     * @param $id
     * @return mixed
     */
    public static function delete($id)
    {
        $webhook = OpenpayInstance::getInstance()->webhooks->get($id);

        return $webhook->delete();
    }

    /**
     * @param array $options
     * @return mixed
     */
    public static function all(array $options = [])
    {
        return OpenpayInstance::getInstance()->webhooks->getList($options);
    }
}
