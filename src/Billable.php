<?php

namespace Perafan\CashierOpenpay;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Perafan\CashierOpenpay\Customer as OpenpayCustomer;

trait Billable
{
    /**
     * @param $amount
     * @param $options
     * @return mixed
     */
    public function charge($amount, $options)
    {
        $options = array_merge([
            'amount' => $amount,
        ], $options);

        $customer = $this->asOpenpayCustomer();

        return $customer->charges->create($options);
    }

    /**
     * @param $charge_id
     * @param null $amount
     * @param string $description
     * @return mixed
     */
    public function refund($charge_id, $amount = null, $description = '')
    {
        $refundData = [
            'description' => $description,
            'amount' => $amount,
        ];

        $customer = $this->asOpenpayCustomer();
        $charge = $customer->charges->get($charge_id);

        return $charge->refund($refundData);
    }

    /**
     * @param $plan_id
     * @param string $name
     * @param array $options
     * @return mixed
     */
    public function newSubscription($plan_id, $name = 'default', $options = [])
    {
        $options = array_merge([
            'plan_id' => $plan_id,
        ], $options);

        $customer = $this->asOpenpayCustomer();
        $openpay_subscription = $customer->subscriptions->add($options);

        $subscription = $this->subscriptions()->fill([
            'name' => $name,
            'openpay_id' => $openpay_subscription->id,
            'openpay_status' => $openpay_subscription->status,
            'openpay_plan' => $plan_id,
            'trial_ends_at' => $openpay_subscription->trial_end_date,
            'ends_at' => $openpay_subscription->period_end_date,
        ]);

        $subscription->save();

        return $subscription;
    }

    public function onPlan()
    {
    }

    public function onTrial()
    {
    }

    public function onGenericTrial()
    {
    }

    public function subscribed()
    {
    }

    public function subscribedToPlan()
    {
    }

    public function subscription()
    {
    }

    /**
     * Get all of the subscriptions for the Paddle model.
     *
     * @return HasMany
     */
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class, $this->getForeignKey());
    }

    /**
     * Determine if the entity has a OpenPay customer ID.
     *
     * @return bool
     */
    public function hasOpenpayId()
    {
        return ! is_null($this->openpay_id);
    }

    /**
     * Create a Openpay customer for the given Openpay model.
     *
     * @param array $options
     * @return OpenpayCustomer
     */
    public function createAsOpenpayCustomer(array $options = [])
    {
        if ($this->hasOpenpayId()) {
            return $this->asOpenpayCustomer();
        }

        $options = array_key_exists('name', $options) ? $options : array_merge($options, ['name' => $this->name]);
        $options = array_key_exists('email', $options) ? $options : array_merge($options, ['email' => $this->email]);
        $options = array_key_exists('external_id', $options) ? $options : array_merge($options, ['external_id' => $this->id]);

        $customer = OpenpayCustomer::create($options);

        $this->openpay_id = $customer->id;
        $this->save();

        return $customer;
    }

    /**
     * Get the Openpay customer for the Openpay model.
     */
    public function asOpenpayCustomer()
    {
        return OpenpayCustomer::find($this->openpay_id);
    }
}
