# CashierOpenpay

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Total Downloads][ico-downloads]][link-downloads]
[![Build Status][ico-travis]][link-travis]
[![StyleCI][ico-styleci]][link-styleci]


## Installation

Require the Cashier package for Openpay with Composer:

```bash
composer require perafan/cashier-openpay
```

| CashierOpenpay | Laravel  |
| :------------: |:--------:| 
| 1.X            | 7.X      | 
| 2.X            | 8.X      |

Run to publish migrations, WebHookController and config file.

```bash
php artisan vendor:publish --tag="cashier-openpay-migrations"
php artisan vendor:publish --tag="cashier-openpay-configs"
php artisan vendor:publish --tag="cashier-openpay-webhook-controller"
```

The Cashier service provider registers its own database migration directory, so remember to migrate your database after installing the package. 
The Cashier migrations will add several columns to your users table as well as create a new subscriptions table to hold all of your customer's subscriptions:

``` bash
php artisan migrate
```

## Configuration

### Billable Model

Add the `Billable` trait to your model definition.
`Billable` trait provides methods to allow yo to perform common billing tasks (creating subscriptions, add payment method information, creating charges ,etc.)

```php
use Perafan\CashierOpenpay\Billable;

class User extends Authenticatable
{
    use Billable;
}
```

Cashier assumes your Billable model will be the `App\Models\User class that ships with Laravel. If you wish to change this you can specify a different model in your `.env` file:

```dotenv
OPENPAY_MODEL=App\Models\User
```

### API Keys
Next, you should configure your Openpay keys in your .env file. You can retrieve your Openpay API keys from the Openpay control panel.

```dotenv
OPENPAY_PUBLIC_KEY=-your-openpay-public-key-
OPENPAY_PRIVATE_KEY=-your-openpay-private-key-
OPENPAY_ID=-your-openpay-id-
```

### Environment

By convenience and security, the sandbox mode is activated by default in the client library. This allows you to test your own code when implementing Openpay, before charging any credit card in production environment. 

```dotenv
OPENPAY_PRODUCTION_MODE=false
```

### Logging

Cashier allows you to specify the log channel to be used when logging all Openpay related exceptions.

```dotenv
OPENPAY_LOG_ERRORS=true
```

### Show openpay errors (Optional)`

If you want to catch all the openpay exceptions add in your `app/Exceptions/Handler.php` 

```php
<?php

namespace App\Exceptions;

use Perafan\CashierOpenpay\OpenpayExceptionsHandler;
...

class Handler extends ExceptionHandler
{
    use OpenpayExceptionsHandler;

    ...

    public function render($request, Throwable $exception)
    {
        if ($this->isOpenpayException($exception)) {
            return $this->renderOpenpayException($request, $exception);
        }
        return parent::render($request, $exception);
    }
}
```

To render the error response in blade you could use the follow snippets.

#### Show errors with [bootstrap](https://getbootstrap.com/)

```blade
@if($errors->cashier->isNotEmpty())
    <div class="alert alert-danger" role="alert">
        @foreach ($errors->cashier->keys() as $key)
            <strong>{{ $key }} :</strong> {{ $errors->cashier->get($key)[0] }} <br>
        @endforeach
    </div>
@endif
```

#### Show errors with [tailwindcss](https://tailwindcss.com/)

```blade
@if($errors->cashier->isNotEmpty())
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
        @foreach ($errors->cashier->keys() as $key)
            <strong class="font-bold">{{ $key }} :</strong> {{ $errors->cashier->get($key)[0] }} <br>
        @endforeach
    </div>
@endif
```

#### Your own Openpay Exceptions Handler (Optional)

You can modify the response creating your own handler.

```php
trait MyOpenpayExceptionsHandler
{
    use OpenpayExceptionsHandler {
        OpenpayExceptionsHandler::renderOpenpayException as parentRenderOpenpayException;
    }
    
    public function renderOpenpayException(Request $request, OpenpayApiError $exception)
    {
        $this->parentRenderOpenpayException($request, $exception);
        
        //your code
    }
} 
```

## Use

### Customers


**On a User:**

Add a new customer to a merchant:

```php
$user->createAsOpenpayCustomer();

//Or you can send additional data

$data = [
    'name' => 'Teofilo',
    'last_name' => 'Velazco',
    'phone_number' => '4421112233',
    'address' => [
        'line1' => 'Privada Rio No. 12',
        'line2' => 'Co. El Tintero',
        'line3' => '',
        'postal_code' => '76920',
        'state' => 'Querétaro',
        'city' => 'Querétaro.',
        'country_code' => 'MX'
    ]
];

$openpay_customer = $user->createAsOpenpayCustomer($data);
````

Get a customer:
```php
$openpay_customer = $user->asOpenpayCustomer();
```

Update a customer:
```php
$openpay_customer  = $user->asOpenpayCustomer();
$openpay_customer->name = 'Juan';
$openpay_customer->last_name = 'Godinez';
$openpay_customer->save();
```

Delete a customer:
```php
$openpay_customer = $user->asOpenpayCustomer();
$openpay_customer->delete();
```

**On a merchant:**

Add a new customer to a merchant:

```php
use Perafan\CashierOpenpay\Openpay\Customer as OpenpayCustomer;

OpenpayCustomer::create([
    'name' => 'Teofilo',
    'last_name' => 'Velazco',
    'email' => 'teofilo@payments.com',
    'phone_number' => '4421112233',
    'address' => [
        'line1' => 'Privada Rio No. 12',
        'line2' => 'Co. El Tintero',
        'line3' => '',
        'postal_code' => '76920',
        'state' => 'Querétaro',
        'city' => 'Querétaro.',
        'country_code' => 'MX'
    ]
]);
````

Get a customer:
```php
use Perafan\CashierOpenpay\Openpay\Customer as OpenpayCustomer;

$customer = OpenpayCustomer::find('a9ualumwnrcxkl42l6mh');
```

Get the list of customers:
```php
use Perafan\CashierOpenpay\Openpay\Customer as OpenpayCustomer;

$customer_list = OpenpayCustomer::all();

// with filters

$filters = [
	'creation[gte]' => '2020-01-01',
	'creation[lte]' => '2020-12-31',
	'offset' => 0,
	'limit' => 5
];

$customer_list = OpenpayCustomer::all($filters);
```

Update a customer:
```php
use Perafan\CashierOpenpay\Openpay\Customer as OpenpayCustomer;

$customer = OpenpayCustomer::find('a9ualumwnrcxkl42l6mh');
$customer->name = 'Juan';
$customer->last_name = 'Godinez';
$customer->save();
```

Delete a customer:
```php
use Perafan\CashierOpenpay\Openpay\Customer as OpenpayCustomer;

$customer = OpenpayCustomer::find('a9ualumwnrcxkl42l6mh');
$customer->delete();
```

#### Cards ####

**On a user:**

Add a card:
```php
$card_data = [
	'holder_name' => 'Teofilo Velazco',
	'card_number' => '4916394462033681',
	'cvv2' => '123',
	'expiration_month' => '12',
	'expiration_year' => '15'
];

$card = $user->addCard($card_data);

// with token

$card_data = [
    'token_id' => 'tokgslwpdcrkhlgxqi9a',
    'device_session_id' => '8VIoXj0hN5dswYHQ9X1mVCiB72M7FY9o'
];

$card = $user->addCard($card_data);

// with address

$address_data = [
    'line1' => 'Privada Rio No. 12',
    'line2' => 'Co. El Tintero',
    'line3' => '',
    'postal_code' => '76920',
    'state' => 'Querétaro',
    'city' => 'Querétaro.',
    'country_code' => 'MX'
];

$card = $user->addCard($card_data, $address_data);
```

Get a card:
```php
use Perafan\CashierOpenpay\Card;

$card = $user->cards->first;
//or
$card = Card::find(1);

$openpay_card = $card->asOpenpayCard();
```

Get user cards:
```php
use Perafan\CashierOpenpay\Card;

$cards = $user->cards;
// or
$cards = Card::where('user_id', $user->id)->get();
```

Get user cards from Openpay
```php
use Perafan\CashierOpenpay\Card;
use Perafan\CashierOpenpay\Openpay\Card as OpenpayCard;

$cards = $user->cards;

$openpay_cards = $cards->map(function($card) {
    return $card->asOpenpayCard();
});

// or

$cards = Card::where('user_id', $user->id)->get();

$openpay_cards = $cards->map(function($card) {
    return $card->asOpenpayCard();
});

// or 

$openpay_customer = $user->asOpenpayCustomer();

$openpay_cards = OpenpayCard::all([], $openpay_customer);

// with filters

$filters = [
	'creation[gte]' => '2020-01-01',
	'creation[lte]' => '2020-12-31',
	'offset' => 0,
	'limit' => 5
];

$openpay_cards = OpenpayCard::all($filters, $openpay_customer);
```

Delete a card
```php
use Perafan\CashierOpenpay\Card;

$card = $user->cards->first;
//or
$card = Card::find(1);

$openpay_card = $card->asOpenpayCard();
$deleted_card = $openpay_card->delete();

if (!is_array($deleted_card)) {
    //The card was deleted in Openpay
    $card->delete();
}
```

**On a merchant:**

Add a card:
```php
use Perafan\CashierOpenpay\Openpay\Card as OpenpayCard;

$card_data = [
	'holder_name' => 'Teofilo Velazco',
	'card_number' => '4916394462033681',
	'cvv2' => '123',
	'expiration_month' => '12',
	'expiration_year' => '15'
];

$openpay_card = OpenpayCard::add($card_data);

// with token

$card_data = [
    'token_id' => 'tokgslwpdcrkhlgxqi9a',
    'device_session_id' => '8VIoXj0hN5dswYHQ9X1mVCiB72M7FY9o'
];

$openpay_card = OpenpayCard::add($card_data);

// with address

$address_data = [
    'line1' => 'Privada Rio No. 12',
    'line2' => 'Co. El Tintero',
    'line3' => '',
    'postal_code' => '76920',
    'state' => 'Querétaro',
    'city' => 'Querétaro.',
    'country_code' => 'MX'
];

$card_data['address'] = $address_data;

$openpay_card = OpenpayCard::add($card_data);
```

Get a card:
```php
use Perafan\CashierOpenpay\Openpay\Card as OpenpayCard;

$openpay_card = OpenpayCard::find('k9pn8qtsvr7k7gxoq1r5');
```

Get the list of cards:
```php
use Perafan\CashierOpenpay\Openpay\Card as OpenpayCard;

$openpay_card = OpenpayCard::all();

// with filters

$filters = [
	'creation[gte]' => '2020-01-01',
	'creation[lte]' => '2020-12-31',
	'offset' => 0,
	'limit' => 5
];

$openpay_card = OpenpayCard::all($filters);
```

Delete a card:
```php
use Perafan\CashierOpenpay\Openpay\Card as OpenpayCard;

$openpay_card = OpenpayCard::find('k9pn8qtsvr7k7gxoq1r5');
$openpay_card->delete();
//Card was not delete on your database, only was deleted in openpay
```
	
#### Bank Accounts ####

Add a bank account to a customer:
```php
$bank_data = [
	'clabe' => '072910007380090615',
	'alias' => 'Cuenta principal',
	'holder_name' => 'Teofilo Velazco'
];

$bank_account = $user->addBankAccount($bank_data);
```

Get a bank account
```php
use Perafan\CashierOpenpay\BankAccount;

$bank_account = $user->bank_accounts->first;
// or
$bank_account = BankAccount::where('user_id', $user->id)->first();

$openpay_bank_account = $bank_account->asOpenpayBankAccount();
```

Get user bank accounts:
```php
use Perafan\CashierOpenpay\BankAccount;

$bank_accounts = $user->bank_accounts;
// or
$bank_accounts = BankAccount::where('user_id', $user->id)->get();
```

Get user bank accounts from Openpay:
```php
use Perafan\CashierOpenpay\BankAccount;
use Perafan\CashierOpenpay\Openpay\BankAccount as OpenpayBankAccount;

$bank_accounts = $user->bank_accounts;

$openpay_bank_accounts = $bank_accounts->map(function($bank_account) {
    return $bank_account->asOpenpayBankAccount();
});

// or

$bank_accounts = BankAccount::where('user_id', $user->id)->get();

$openpay_bank_accounts = $bank_accounts->map(function($bank_account) {
    return $bank_account->asOpenpayBankAccount();
});

// or 

$openpay_customer = $user->asOpenpayCustomer();

$openpay_bank_accounts = OpenpayBankAccount::all([], $openpay_customer);

// with filters

$filters = [
	'creation[gte]' => '2020-01-01',
	'creation[lte]' => '2020-12-31',
	'offset' => 0,
	'limit' => 5
];

$openpay_bank_accounts = OpenpayBankAccount::all($filters, $openpay_customer);
```

Delete a bank account:
```php
use Perafan\CashierOpenpay\BankAccount;

$bank_account = $user->bank_accounts->first;
// or
$bank_account = BankAccount::where('user_id', $user->id)->first();

$openpay_bank_account = $bank_account->asOpenpayBankAccount();

$deleted_bank_account = $openpay_bank_account->delete();

if (!is_array($deleted_bank_account)) {
    //The card was deleted in Openpay
    $bank_account->delete();
}
```

	
#### Charges ####

**On a Customer:**

Make a charge on a customer:
```php
$charge_data = [
	'source_id' => 'tvyfwyfooqsmfnaprsuk',
	'method' => 'card',
	'currency' => 'MXN',
    'description' => 'Cargo inicial a mi merchant',
    'order_id' => 'oid-00051',
    'device_session_id' => 'kR1MiQhz2otdIuUlQkbEyitIqVMiI16f',
];

$openpay_charge = $user->charge(100, $charge_data);
```

Get a charge:
```php
use Perafan\CashierOpenpay\Openpay\Charge as OpenpayCharge;

$openpay_charge = OpenpayCharge::find('a9ualumwnrcxkl42l6mh');
```

Get list of charges per user:
```php
use Perafan\CashierOpenpay\Openpay\Charge as OpenpayCharge;

$filters = [
	'creation[gte]' => '2020-01-01',
	'creation[lte]' => '2020-12-31',
	'offset' => 0,
	'limit' => 5
];

$openpay_customer = $user->asOpenpayCustomer();

$openpay_charge = OpenpayCharge::all($filters, $openpay_customer);
```

Make a capture:
```php
use Perafan\CashierOpenpay\Openpay\Charge as OpenpayCharge;

$capture_data = ['amount' => 150.00];

$openpay_charge = OpenpayCharge::find('a9ualumwnrcxkl42l6mh');

$openpay_charge->capture($capture_data);
```

Make a refund:
```php
// Send charge id as first param 
$charge_id = 'tvyfwyfooqsmfnaprsuk';
$user->refund($charge_id);
//or

$description = 'Reembolso';
$user->refund($charge_id, $description);

//or
$amount = 150.00;
$user->refund($charge_id, $description, $amount);
```

**On a Merchant:**

Make a charge on a merchant:
```php
use Perafan\CashierOpenpay\Openpay\Charge as OpenpayCharge;

$charge_data = [
	'method' => 'card',
	'source_id' => 'krfkkmbvdk3hewatruem',
	'amount' => 100,
	'description' => 'Cargo inicial a mi merchant',
	'order_id' => 'ORDEN-00071'
];

$openpay_charge = OpenpayCharge::create($charge_data);
```
	
Get a charge:
```php
use Perafan\CashierOpenpay\Openpay\Charge as OpenpayCharge;

$openpay_charge = OpenpayCharge::find('tvyfwyfooqsmfnaprsuk');
```
	
Get list of charges:
```php
use Perafan\CashierOpenpay\Openpay\Charge as OpenpayCharge;

$openpay_charges = OpenpayCharge::all();

// with filters
$filters = [
	'creation[gte]' => '2020-01-01',
	'creation[lte]' => '2020-12-31',
	'offset' => 0,
	'limit' => 5
];

$openpay_charges = OpenpayCharge::all($filters);
```
	
Make a capture:
```php
use Perafan\CashierOpenpay\Openpay\Charge as OpenpayCharge;

$capture_data = ['amount' => 150.00];

$openpay_charge = OpenpayCharge::find('tvyfwyfooqsmfnaprsuk');
$capture_data->capture($capture_data);
```
	
Make a refund:
```php
use Perafan\CashierOpenpay\Openpay\Charge as OpenpayCharge;

$refund_data = ['description' => 'Devolución'];

$openpay_charge = OpenpayCharge::find('tvyfwyfooqsmfnaprsuk');
$openpay_charge->refund($refund_data);
```

#### Transfers ####

Make a transfer:
```php
$transfer_data = [
	'customer_id' => 'aqedin0owpu0kexr2eor',
	'amount' => 12.50,
	'description' => 'Cobro de Comisión',
	'order_id' => 'ORDEN-00061'
];

$openpay_customer = $user->asOpenpayCustomer();
$transfer = $openpay_customer->transfers->create($transfer_data);
```
	
Get a transfer:
```php
$openpay_customer = $user->asOpenpayCustomer();
$transfer = $openpay_customer->transfers->get('tyxesptjtx1bodfdjmlb');
```

Get list of transfers:
```php
$filters = [
	'creation[gte]' => '2020-01-01',
	'creation[lte]' => '2020-12-31',
	'offset' => 0,
	'limit' => 5
];

$openpay_customer = $user->asOpenpayCustomer();
$transfer_list = $openpay_customer->transfers->getList($filters);
```

#### Payouts ####

**On a Customer:**

Make a payout on a customer:
```php
$payout_data = [
	'method' => 'card',
	'destination_id' => 'k9pn8qtsvr7k7gxoq1r5',
	'amount' => 1000,
	'description' => 'Retiro de saldo semanal',
	'order_id' => 'ORDEN-00062'
];

$openpay_customer = $user->asOpenpayCustomer();
$payout = $openpay_customer->payouts->create($payout_data);
```
	
Get a payout:
```php
$openpay_customer = $user->asOpenpayCustomer();
$payout = $openpay_customer->payouts->get('tysznlyigrkwnks6eq2c');
```
	
Get list pf payouts:
```php
$filters = [
	'creation[gte]' => '2020-01-01',
	'creation[lte]' => '2020-12-31',
	'offset' => 0,
	'limit' => 5
];

$openpay_customer = $user->asOpenpayCustomer();
$payout_list = $customer->payouts->getList($filters);
```

#### Fees ####
Pending ...

#### Plans ####

Add a plan:
```php
use Perafan\CashierOpenpay\Openpay\Plan as OpenpayPlan;

$plan_data = [
	'amount' => 150.00,
	'status_after_retry' => 'cancelled',
	'retry_times' => 2,
	'name' => 'Plan Curso Verano',
	'repeat_unit' => 'month',
	'trial_days' => '30',
	'repeat_every' => '1',
	'currency' => 'MXN'
];

$openpay_plan = OpenpayPlan::add($plan_data);
```
	
Get a plan:
```php
use Perafan\CashierOpenpay\Openpay\Plan as OpenpayPlan;

$openpay_plan = OpenpayPlan::find('pduar9iitv4enjftuwyl');
```
	
Get list of plans: 
```php
use Perafan\CashierOpenpay\Openpay\Plan as OpenpayPlan;

$openpay_plans = OpenpayPlan::all();
// with filters
$filters = [
	'creation[gte]' => '2020-01-01',
	'creation[lte]' => '2020-12-31',
	'offset' => 0,
	'limit' => 5
];

$openpay_plans = OpenpayPlan::all($filters);
```

Update a plan:
```php
use Perafan\CashierOpenpay\Openpay\Plan as OpenpayPlan;

$openpay_plan = OpenpayPlan::find('pduar9iitv4enjftuwyl');
$openpay_plan->name = 'Plan Curso de Verano 2021';
$openpay_plan->save();
```
	
Delete a plan:
```php
use Perafan\CashierOpenpay\Openpay\Plan as OpenpayPlan;

$openpay_plan = OpenpayPlan::find('pduar9iitv4enjftuwyl');
$openpay_plan->delete();
```

Get list of subscriptors of a plan: 
```php
use Perafan\CashierOpenpay\Openpay\Plan as OpenpayPlan;

$openpay_plan = OpenpayPlan::find('pduar9iitv4enjftuwyl');

$filters = [
	'creation[gte]' => '2020-01-01',
	'creation[lte]' => '2020-12-31',
	'offset' => 0,
	'limit' => 5
];

$subscription_list = $openpay_plan->subscriptions->getList($filters);
```

#### Subscriptions ####

Add a subscription:
```php
$plan_id = 'pduar9iitv4enjftuwyl';

$subscription = $user->newSubscription($plan_id);

// Add the name of subscription
$options = [
	'trial_end_date' => '2021-01-01', 
	'card_id' => 'konvkvcd5ih8ta65umie'
];

$subscription = $user->newSubscription($plan_id, $options);

// Add the name of subscription
$name = 'plan_verano_2021';

$subscription = $user->newSubscription($plan_id, $options, $name);
```

Checking Subscription Status
```php
$name = 'plan_verano_2021';
$user->subscribed($name);

$name_2027 = 'plan_verano_2027';
$user->subscribed($name_2027);

$plan_id = 'pduar9iitv4enjftuwyl';
$user->subscribed($name, $plan_id); 

$user->subscribed($name, 'ptyui9iit40nfwftuwyl'); 
```

```php
$plans = [
    'pduar9iitv4enjftuwyl',
    'ptyui9iit40nfwftuwyl'
];

$user->subscribedToPlan($plans);
```

Subscription Trial
```php
$subscription = $user->subscription();

$subscription->onTrial();
```


Checking User Trial
```php
$subscription_name = 'plan_verano_2027';

$user->onTrial($subscription_name); 
```

Get a subscription:
```php
use Perafan\CashierOpenpay\Subscription;
$subscription = $user->subscriptions->first;
// or
$subscription = Subscription::find('s7ri24srbldoqqlfo4vp');

$subscription->asOpenpaySubscription();
```

Get list of subscriptions:
```php
$openpay = Openpay::getInstance('moiep6umtcnanql3jrxp', 'sk_3433941e467c1055b178ce26348b0fac');

$filters = [
	'creation[gte]' => '2020-01-01',
	'creation[lte]' => '2020-12-31',
	'offset' => 0,
	'limit' => 5
];

$customer = $openpay->customers->get('a9ualumwnrcxkl42l6mh');
$subscriptionList = $customer->subscriptions->getList($filters);
```
	
Update a subscription:
```php
$openpay = Openpay::getInstance('moiep6umtcnanql3jrxp', 'sk_3433941e467c1055b178ce26348b0fac');

$customer = $openpay->customers->get('a9ualumwnrcxkl42l6mh');
$subscription = $customer->subscriptions->get('s7ri24srbldoqqlfo4vp');
$subscription->trial_end_date = '2021-12-31';
$subscription->save();
```
	
Delete a subscription:
```php
$openpay = Openpay::getInstance('moiep6umtcnanql3jrxp', 'sk_3433941e467c1055b178ce26348b0fac');

$customer = $openpay->customers->get('a9ualumwnrcxkl42l6mh');
$subscription = $customer->subscriptions->get('s7ri24srbldoqqlfo4vp');
$subscription->delete();
```

## Openpay SDK

Many of Cashier's objects are wrappers around Openpay SDK objects. If you would like to interact with the Openpay objects directly, you may conveniently retrieve them using the `asOpenpay...` methods:

```php

$openpayCustomer = $user->asOpenpayCustomer();

$openpayCustomer->name = 'Pedro';

$openpayCustomer->save();

$openpaySubscription = $subscription->asOpenpaySubscription();

$subscription->trial_end_date = '2021-12-31';

$openpaySubscription->save();
```

## Testing

To get started, add the testing version of your Openpay keys to your phpunit.xml file:

```xml
<env name="OPENPAY_PUBLIC_KEY" value=""/>
<env name="OPENPAY_PRIVATE_KEY" value=""/>
<env name="OPENPAY_ID" value=""/>
```

Then you can run on your term

``` bash
$ composer install
$ vendor/bin/phpunit
```

## Contributing

Please see [contributing.md](contributing.md) for details and a todolist.

## Security

If you discover any security related issues, please email pedro.perafan.carrasco@gmail.com instead of using the issue tracker.

## Credits

- [Pedro Perafán Carrasco][link-author]

## License

MIT. Please see the [license file](license.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/perafan/cashier-openpay.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/perafan/cashier-openpay.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/Perafan18/cashier-openpay/master.svg?style=flat-square
[ico-styleci]: https://styleci.io/repos/133202140/shield

[link-packagist]: https://packagist.org/packages/perafan/cashier-openpay
[link-downloads]: https://packagist.org/packages/perafan/cashier-openpay
[link-travis]: https://travis-ci.org/github/Perafan18/cashier-openpay
[link-styleci]: https://styleci.io/repos/133202140
[link-author]: https://github.com/perafan18


[https://www.openpay.mx/docs/api/#devolver-un-cargo]: https://www.openpay.mx/docs/api/#devolver-un-cargo
