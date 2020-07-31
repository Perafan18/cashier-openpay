<?php

namespace Perafan\CashierOpenpay;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Perafan\CashierOpenpay\Openpay\Subscription as OpenpaySubscription;

class Subscription extends Model
{
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_PAST_DUE = 'past_due';
    const STATUS_TRIAL = 'trial';
    const STATUS_UNPAID = 'unpaid';
    const STATUS_ACTIVE = 'active';

    protected $fillable = [
        'name', 'user_id', 'openpay_id', 'openpay_status', 'openpay_plan', 'trial_ends_at', 'ends_at',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'trial_ends_at', 'ends_at', 'created_at', 'updated_at',
    ];

    /**
     * Determine if the subscription is active, on trial, or within its grace period.
     *
     * @return bool
     */
    public function valid()
    {
        return $this->active() || $this->onTrial() || $this->onGracePeriod();
    }

    /**
     * Determine if the subscription is active.
     *
     * @return bool
     */
    public function active()
    {
        return (is_null($this->ends_at) || $this->onGracePeriod()) &&
            ! $this->pastDue();
    }

    /**
     * Determine if the subscription is within its grace period after cancellation.
     *
     * @return bool
     */
    public function onGracePeriod()
    {
        return $this->ends_at && $this->ends_at->isFuture();
    }

    /**
     * Determine if the subscription is within its trial period.
     *
     * @return bool
     */
    public function onTrial()
    {
        return $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    /**
     * Filter query by on trial.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return void
     */
    public function scopeOnTrial($query)
    {
        $query->whereNotNull('trial_ends_at')->where('trial_ends_at', '>', Carbon::now());
    }

    /**
     * Determine if the subscription is past due.
     *
     * @return bool
     */
    public function pastDue()
    {
        return $this->openpay_status === self::STATUS_PAST_DUE;
    }

    /**
     * Filter query by past due.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return void
     */
    public function scopePastDue($query)
    {
        $query->where('openpay_status', self::STATUS_PAST_DUE);
    }

    /**
     * Determine if the subscription is past due.
     *
     * @return bool
     */
    public function unpaid()
    {
        return $this->openpay_status === self::STATUS_UNPAID;
    }

    /**
     * Filter query by past due.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return void
     */
    public function scopeUnpaid($query)
    {
        $query->where('openpay_status', self::STATUS_UNPAID);
    }

    /**
     * Determine if the subscription is no longer active.
     *
     * @return bool
     */
    public function cancelled()
    {
        return ! is_null($this->ends_at);
    }

    /**
     * Filter query by cancelled.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return void
     */
    public function scopeCancelled($query)
    {
        $query->whereNotNull('ends_at');
    }

    /**
     * Filter query by not cancelled.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return void
     */
    public function scopeNotCancelled($query)
    {
        $query->whereNull('ends_at');
    }

    /**
     * Determine if the subscription has ended and the grace period has expired.
     *
     * @return bool
     */
    public function ended()
    {
        return $this->cancelled() && ! $this->onGracePeriod();
    }

    /**
     * Filter query by ended.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return void
     */
    public function scopeEnded($query)
    {
        $query->cancelled()->notOnGracePeriod();
    }

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
     * Cancel the subscription at the end of the billing period.
     *
     * @return $this
     */
    public function cancel()
    {
        $subscription = $this->asOpenpaySubscription();

        $subscription->cancel_at_period_end = true;

        $subscription = $subscription->save();

        $this->openpay_status = self::STATUS_CANCELLED;

        if ($this->onTrial()) {
            $this->ends_at = $this->trial_ends_at;
        } else {
            $this->ends_at = $subscription->charge_date;
        }

        $this->save();

        return $this;
    }

    /**
     * Cancel the subscription immediately.
     *
     * @return $this
     */
    public function cancelNow()
    {
        $this->asOpenpaySubscription()->delete();

        $this->fill([
            'openpay_status' => self::STATUS_CANCELLED,
            'ends_at' => Carbon::now(),
        ])->save();

        return $this;
    }

    /**
     * Determine if the subscription has a specific plan.
     *
     * @param  int  $plan
     * @return bool
     */
    public function hasPlan($plan)
    {
        return $this->openpay_plan == $plan;
    }

    /**
     * Get the subscription as a Openpay subscription object.
     *
     * @return \OpenpaySubscription
     */
    public function asOpenpaySubscription()
    {
        $customer = $this->owner->asOpenpayCustomer();

        return OpenpaySubscription::find($this->openpay_id, $customer);
    }
}
