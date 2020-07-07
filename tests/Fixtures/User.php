<?php

namespace Perafan\CashierOpenpay\Tests\Fixtures;

use Perafan\CashierOpenpay\Billable;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use Billable;

    protected $dates = ['trial_ends_at'];

    /**
     * The attributes that are not mass assignable.
     *
     * @var array
     */
    protected $guarded = [];
}
