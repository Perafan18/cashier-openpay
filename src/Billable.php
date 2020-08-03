<?php

namespace Perafan\CashierOpenpay;

use Illuminate\Database\Eloquent\Relations\HasMany;
use OpenpayCustomer;
use Perafan\CashierOpenpay\Openpay\BankAccount as OpenpayBankAccount;
use Perafan\CashierOpenpay\Openpay\Card as OpenpayCard;
use Perafan\CashierOpenpay\Openpay\Charge as OpenpayCharge;
use Perafan\CashierOpenpay\Openpay\Customer;
use Perafan\CashierOpenpay\Openpay\Subscription as OpenpaySubscription;

trait Billable
{
    /**
     * @param $amount
     * @param $options
     * @return mixed
     */
    public function charge($amount, array $options)
    {
        $options = array_merge([
            'amount' => $amount,
        ], $options);

        $customer = $this->asOpenpayCustomer();

        return OpenpayCharge::create($options, $customer);
    }

    /**
     * @param $charge_id
     * @param null $amount
     * @param string $description
     * @return mixed
     */
    public function refund($charge_id, $amount = null, $description = '')
    {
        $refund_data = [
            'description' => $description,
            'amount' => $amount,
        ];

        $customer = $this->asOpenpayCustomer();

        return OpenpayCharge::refund($charge_id, $refund_data, $customer);
    }

    /**
     * @param $plan_id
     * @param string $name
     * @param array $options
     * @return Subscription
     */
    public function newSubscription($plan_id, array $options = [], $name = 'default')
    {
        $options = array_merge([
            'plan_id' => $plan_id,
        ], $options);

        $customer = $this->asOpenpayCustomer();
        $openpay_subscription = OpenpaySubscription::add($options, $customer);

        /** @var Subscription $subscription */
        $subscription = $this->subscriptions()->create([
            'name' => $name,
            'user_id' => $this->id,
            'openpay_id' => $openpay_subscription->id,
            'openpay_status' => $openpay_subscription->status,
            'openpay_plan' => $plan_id,
            'trial_ends_at' => $openpay_subscription->trial_end_date,
            'ends_at' => null,
        ]);

        $subscription->save();

        return $subscription;
    }

    /**
     * @param string $name
     * @param null $plan
     * @return bool
     */
    public function subscribed($name = 'default', $plan = null)
    {
        $subscription = $this->subscription($name);

        if (! $subscription || ! $subscription->valid()) {
            return false;
        }

        return $plan ? $subscription->hasPlan($plan) : true;
    }

    /**
     * @param $plans
     * @param string $name
     * @return bool
     */
    public function subscribedToPlan($plans, $name = 'default')
    {
        $subscription = $this->subscription($name);

        if (! $subscription || ! $subscription->valid()) {
            return false;
        }

        foreach ((array) $plans as $plan) {
            if ($subscription->hasPlan($plan)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $plan
     * @return bool
     */
    public function onPlan($plan)
    {
        return ! is_null($this->subscriptions->first(function (Subscription $subscription) use ($plan) {
            return $subscription->valid() && $subscription->hasPlan($plan);
        }));
    }

    /**
     * Determine if the  model is on trial.
     *
     * @param  string  $name
     * @param  string|null  $plan
     * @return bool
     */
    public function onTrial($name = 'default', $plan = null)
    {
        $subscription = $this->subscription($name);

        if (! $subscription || ! $subscription->onTrial()) {
            return false;
        }

        return $plan ? $subscription->hasPlan($plan) : true;
    }

    /**
     * @param string $name
     * @return Subscription|null
     */
    public function subscription($name = 'default')
    {
        return $this->subscriptions()->where('name', $name)->first();
    }

    /**
     * Get all of the subscriptions for the Openpay model.
     *
     * @return HasMany
     */
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class, $this->getForeignKey());
    }

    /**
     * @param $card_data
     * @param $address
     * @param array $options
     * @return Card
     */
    public function addCard(array $card_data, array $address, array $options = [])
    {
        $card_data['address'] = $address;

        $data = array_merge($card_data, $options);

        $customer = $this->asOpenpayCustomer();
        $card_openpay = OpenpayCard::add($data, $customer);

        /** @var Card $card */
        $card = $this->cards()->create([
            'user_id' => $this->id,
            'openpay_id' => $card_openpay->id,
            'type' => $card_openpay->type,
            'brand' => $card_openpay->brand,
            'holder_name' => $card_openpay->holder_name,
            'card_number' => $card_openpay->card_number,
            'expiration_month' => $card_openpay->expiration_month,
            'expiration_year' => $card_openpay->expiration_year,
            'bank_name' => $card_openpay->bank_name,
            'bank_code' => $card_openpay->bank_code,
        ]);

        $card->save();

        return $card;
    }

    /**
     * @return HasMany
     */
    public function cards()
    {
        return $this->hasMany(Card::class, $this->getForeignKey());
    }

    public function addBankAccount(array $bank_account_data)
    {
        $customer = $this->asOpenpayCustomer();
        $bank_account = OpenpayBankAccount::add($bank_account_data, $customer);

        /** @var BankAccount $bank_account */
        $bank_account = $this->bank_accounts()->create([
            'user_id' => $this->id,
            'openpay_id' => $bank_account->id,
            'holder_name' => $bank_account->holder_name,
            'clabe' => $bank_account->clabe,
            'bank_name' => $bank_account->bank_name,
            'bank_code' => $bank_account->bank_code,
            'alias' => $bank_account->alias,
        ]);

        $bank_account->save();

        return $bank_account;
    }

    /**
     * @return HasMany
     */
    public function bank_accounts()
    {
        return $this->hasMany(BankAccount::class, $this->getForeignKey());
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

        $customer = Customer::add($options);

        $this->openpay_id = $customer->id;
        $this->save();

        return $customer;
    }

    /**
     * Get the Openpay customer for the Openpay model.
     *
     * @return OpenpayCustomer
     */
    public function asOpenpayCustomer()
    {
        if (is_null($this->openpay_id)) {
            $this->createAsOpenpayCustomer();
        }

        return Customer::find($this->openpay_id);
    }
}
