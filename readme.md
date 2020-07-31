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

```php
use Perafan\CashierOpenpay\Openpay\Customer as OpenpayCustomer;

// Create

$customer_data = [
    'external_id' => '',
    'name' => 'customer name',
    'last_name' => '',
    'email' => 'customer_email@me.com',
    'requires_account' => false,
    'phone_number' => '44209087654',
    'address' => [
        'line1' => 'Calle 10',
        'line2' => 'col. san pablo',
        'line3' => 'entre la calle 1 y la 2',
        'state' => 'Queretaro',
        'city' => 'Queretaro',
        'postal_code' => '76000',
        'country_code' => 'MX'
    ]
];

$customer = OpenpayCustomer::add($customer_data);

// Find

$customer = OpenpayCustomer::find('a9ualumwnrcxkl42l6mh');

// Update

$customer = OpenpayCustomer::find('a9ualumwnrcxkl42l6mh');
$customer->name = 'Juan';
$customer->last_name = 'Godinez';
$customer->save();

// Delete

$customer = OpenpayCustomer::delete('a9ualumwnrcxkl42l6mh');
```

## Payment Methods

### Add Cards

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

### Get all cards

```php
$cards = $user->cards();
// or

$cards_data = [
    'creation[gte]' => '2013-01-01',
    'creation[lte]' => '2013-12-31',
    'offset' => 0,
    'limit' => 5
];

$cards = $user->cards($cards_data);
```
[More info](https://www.openpay.mx/docs/api/#listado-de-tarjetas)

### Get a card

```php
$customer = $user->asOpenpayCustomer();
$card = $customer->cards->get('id_card');
```
[More info](https://www.openpay.mx/docs/api/#obtener-una-tarjeta)

### Check points

```php
$cards = $user->cards();
$cards[0]->get('points');
```
[More info](https://www.openpay.mx/docs/api/#consulta-de-puntos)

### Update card

```php
$cards = $user->cards();
$cards[0]->holder_name = 'Mr. Pedro';
$cards[0]->save();
```
[More info](https://www.openpay.mx/docs/api/#consulta-de-puntos)

### Delete card

```php
$cards = $user->cards();
$cards[0]->delete();
```
[More info](https://www.openpay.mx/docs/api/#eliminar-una-tarjeta)

### Create Bank Account

```php
$bank_data_request = [
    'clabe' => '072910007380090615',
    'alias' => 'Cuenta principal',
    'holder_name' => 'Teofilo Velazco'
];

$customer = $user->asOpenpayCustomer();
$bankaccount = $customer->bankaccounts->add($bank_data_request);
```
[More info](https://www.openpay.mx/docs/api/#crear-una-cuenta-bancaria)

### Get Bank Account

```php
$customer = $user->asOpenpayCustomer();
$bankaccount = $customer->bankaccounts->get('');
```
[More info](https://www.openpay.mx/docs/api/#crear-una-cuenta-bancaria)




## Testing

``` bash
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
