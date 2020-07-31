<?php

namespace Perafan\CashierOpenpay;

use Illuminate\Database\Eloquent\Model;
use Perafan\CashierOpenpay\Openpay\Card as OpenpayCard;

class Card extends Model
{
    protected $fillable = [
        'user_id', 'name', 'openpay_id', 'type', 'brand', 'holder_name', 'card_number',
        'expiration_month', 'expiration_year', 'bank_name', 'bank_code'
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
     * Get the subscription as a Openpay card object.
     *
     * @return \OpenpayCard
     */
    public function asOpenpayCard()
    {
        $customer = $this->owner->asOpenpayCustomer();

        return OpenpayCard::find($this->openpay_id, $customer);
    }
}
