<?php

namespace Perafan\CashierOpenpay;

use Illuminate\Database\Eloquent\Model;
use Perafan\CashierOpenpay\Openpay\BankAccount as OpenpayBankAccount;

class BankAccount extends Model
{
    protected $fillable = [
        'user_id', 'openpay_id', 'holder_name', 'clabe', 'bank_name', 'bank_code', 'alias',
    ];

    /**
     * Get the user that owns the subscription.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->owner();
    }

    /**
     * Get the model related to the subscription.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function owner()
    {
        $model = config('cashier_openpay.model');

        return $this->belongsTo($model, (new $model)->getForeignKey());
    }

    /**
     * Get the subscription as a Openpay bank account object.
     *
     * @return \OpenpayBankAccount
     */
    public function asOpenpayBankAccount()
    {
        $customer = $this->owner->asOpenpayCustomer();

        return OpenpayBankAccount::find($this->openpay_id, $customer);
    }
}
