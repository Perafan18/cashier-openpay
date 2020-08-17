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

Cashier assumes your Billable model will be the App\User class that ships with Laravel. If you wish to change this you can specify a different model in your `.env` file:

```dotenv
OPENPAY_MODEL=App\User
```

### API Keys
Next, you should configure your Openpay keys in your .env file. You can retrieve your Stripe API keys from the Openpay control panel.

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

### OpenpayJS

Paddle relies on its own JavaScript library to initiate the Paddle checkout widget. You can load the JavaScript library by placing the @paddleJS directive right before your application layout's closing </head> tag:

``` html
<!DOCTYPE html>
<html>
<head>
    ...
    @openpayJSLoad
</head>
<body>
    ...

    @openpayJSInit
    // or if you are using Jquery
    @openpayJqueryJSInit
</body>
</html>
```

### Logging

If you want to catch all the openpay exceptions add in your `app/Exceptions/Handler.php` 

```php
<?php

namespace App\Exceptions;

use Perafan\CashierOpenpay\Traits\OpenpayExceptionsHandler;
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

Cashier allows you to specify the log channel to be used when logging all Openpay related exceptions.:

```dotenv
OPENPAY_LOG_ERRORS=true
```

### Show openpay errors (Optional)

To render the error response in blade you could use the follow snippets.
**Is necessary use the OpenpayExceptionsHandler**

#### Using [bootstrap](https://getbootstrap.com/)

```
@if($errors->cashier->isNotEmpty())
    <div class="alert alert-danger" role="alert">
        @foreach ($errors->cashier->keys() as $key)
            <strong>{{ $key }} :</strong> {{ $errors->cashier->get($key)[0] }} <br>
        @endforeach
    </div>
@endif
```

#### Using [tailwindcss](https://tailwindcss.com/)

```
@if($errors->cashier->isNotEmpty())
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
        @foreach ($errors->cashier->keys() as $key)
            <strong class="font-bold">{{ $key }} :</strong> {{ $errors->cashier->get($key)[0] }} <br>
        @endforeach
    </div>
@endif
```

You can modify the response creating your own handler.

#### Your own Openpay Exceptions Handler (Optional)

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

## Customers

### Creating Customers

Occasionally, you may wish to create a Stripe customer without beginning a subscription. You may accomplish this using the createAsStripeCustomer method:

```php
$openpayCustomer = $user->createAsOpenpayCustomer();
```

Once the customer has been created in Stripe, you may begin a subscription at a later date. You can also use an optional $options array to pass in any additional parameters which are supported by the Stripe API:

```php
$options = [
    'phone_number' => '3321456789',
];

$openpayCustomer = $user->createAsOpenpayCustomer($options);
```

You may use the asStripeCustomer method if you want to return the customer object if the billable entity is already a customer within Stripe:


```php
$openpayCustomer = $user->asOpenpayCustomer();
```

### Updating Customers
Occasionally, you may wish to update the Stripe customer directly with additional information. You may accomplish this using the updateStripeCustomer method:

```php
$openpayCustomer = $user->asOpenpayCustomer();

$openpayCustomer->name = 'Pedro';
$openpayCustomer->phone_number = '332165987845';

$openpayCustomer->save();
```

## Cards
Coming Soon ...

### Storing Card
```php
$card_data = [
    'holder_name' => 'Taylor Otwell',
    'card_number' => '4111111111111111',
    'cvv2' => '123',
    'expiration_month' => '12',
    'expiration_year' => '30',
];

$address = [
   'line1' => 'Avenida Carranza 1115',
   'postal_code' => '78230',
   'state' => 'San Luis Potosí',
   'city' => 'San Luis Potosí',
   'country_code' => 'MX'
];

$extra_data = [
    'device_session_id' => 'qwertyuiopasdfghjklñ1234567890',
];

$card = $user->addCard($card_data, $address, $extra_data);
```

### Retrieving Cards
Coming Soon ...
### Deleting Card
Coming Soon ...

## Bank Accounts
Coming Soon ...

### Storing Bank Account
```php
$bank_data_request = [
    'clabe' => '072910007380090615',
    'alias' => 'Cuenta principal',
    'holder_name' => 'Teofilo Velazco'
];

$bank_account = $user->addBankAccount($bank_data_request);
```
### Retrieving Bank Accounts
Coming Soon ...

### Deleting Bank Account
Coming Soon ...

## Subscriptions
Coming Soon ...
### Creating Subscriptions
Coming Soon ...
### Checking Subscription Status
Coming Soon ...
### Updating Payment Information
Coming Soon ...
### Cancelling Subscriptions
Coming Soon ...
### Resuming Subscriptions
Coming Soon ...

## Subscription Trials
Coming Soon ...
### With Payment Method Up Front
Coming Soon ...
### Extending Trials

## Handling Openpay Webhooks
Coming Soon ...
### Defining Webhook Event Handlers
Coming Soon ...
### Failed Subscriptions
Coming Soon ...
### Verifying Webhook Signatures
Coming Soon ...

## Single Charges

### Simple Charge

Charges are non-recurring payments, as are subscriptions.
The charges can be created by identifying the card, the token, and submitting the credit card information.

As Merchant
```php
use Perafan\CashierOpenpay\Openpay\Charge as OpenpayCharge;

$data = [
    'method' => 'card',
    'source_id' => 'kqgykn96i7bcs1wwhvgw',
    'amount' => 100,
    'currency' => 'MXN',
    'description' => 'Cargo inicial a mi merchant',
    'order_id' => 'oid-00051',
    'device_session_id' => 'kR1MiQhz2otdIuUlQkbEyitIqVMiI16f',
    'customer' => [
        'name' => 'Juan',
         'last_name' => 'Vazquez Juarez',
         'phone_number' => '4423456723',
         'email' => 'juan.vazquez@empresa.com.mx'
    ];
];

OpenpayCharge::create($data);
```

As User
```php
$amount = 1000;

$data = [
    'method' => 'card',
    'source_id' => 'randomsourceidkrngonfkogplsf',
    'description' => 'Cargo inicial a mi merchant',
    'order_id' => 'oid-00051',
    'device_session_id' => 'randomdevicesessionidjnvjnfogsfp'
];

$user->charge($amount, $data);
```
[Openpay charge documentation][https://www.openpay.mx/docs/api/#devolver-un-cargo]

### Capture Charges

When a charge is created with the param `capture` as `true` is needed confirm the charge.
 
```php
$data = [
    'method' => 'card',
    'source_id' => 'kqgykn96i7bcs1wwhvgw',
    'amount' => 100,
    'description' => 'Cargo inicial a mi merchant',
    'capture' => true,
    ...
];

// Merchant
$charge = OpenpayCharge::create($data);
// User
$charge = $user->charge($amount, $data);

$capture_data = [
    'amount' => 10.00
];

$charge->capture($capture_data);
```

[Openpay capture documentation][https://www.openpay.mx/docs/api/#confirmar-un-cargo]

### Refunding Charges

If you need to refund a charge of a card charge, you can use the refund method. The amount to be returned will be for the total charge or a lesser amount. 

As Merchant
```php
$charge_id = $charge->id;

$refund_data = [
    'description' => 'Devolución',
    'amount' => 500
];

OpenpayCharge::refund($charge_id, $refund_data);
```

As User
```php
$charge_id = $charge->id;

$user->refund($charge_id);
```
You can also add description and amount.

```php
$charge_id = $charge->id;
$description = 'Devolución';
$amount = 500;

$user->refund($charge_id, $description, $amount);
```
[Openpay refund documentation][https://www.openpay.mx/docs/api/#devolver-un-cargo]

## Openpay SDK

Many of Cashier's objects are wrappers around Openpay SDK objects. If you would like to interact with the Openpay objects directly, you may conveniently retrieve them using the `asOpenpay...` methods:

```php

$openpayCustomer = $user->asOpenpayCustomer();

$openpayCustomer->name = 'Pedro';

$openpayCustomer->save();

$openpaySubscription = $subscription->asOpenpaySubscription();

$subscription->trial_end_date = '2014-12-31';

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
[ico-styleci]: https://styleci.io/repos/133201440/shield

[link-packagist]: https://packagist.org/packages/perafan/cashier-openpay
[link-downloads]: https://packagist.org/packages/perafan/cashier-openpay
[link-travis]: https://travis-ci.org/github/Perafan18/cashier-openpay
[link-styleci]: https://styleci.io/repos/133201440
[link-author]: https://github.com/perafan18


[https://www.openpay.mx/docs/api/#devolver-un-cargo]: https://www.openpay.mx/docs/api/#devolver-un-cargo
