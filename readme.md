# CashierOpenpay

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Total Downloads][ico-downloads]][link-downloads]
[![Build Status][ico-travis]][link-travis]
[![StyleCI][ico-styleci]][link-styleci]


## Installation

``` bash
$ composer require perafan/cashier-openpay
```

Add in you `.env`

``` env
OPENPAY_PUBLIC_KEY=
OPENPAY_PRIVATE_KEY=
OPENPAY_ID=
OPENPAY_PRODUCTION_MODE=
OPENPAY_LOG_ERRORS=
```


If you want to catch all the exceptions add in your `app/Exceptions/Handler.php` 

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

Publish config file

``` bash
php artisan vendor:publish --tag="cashier-openpay-configs"
```

Publish WebHookController

``` bash
php artisan vendor:publish --tag="cashier-openpay-webhook-controller"
```

Publish Migrations

``` bash
php artisan vendor:publish --tag="cashier-openpay-migrations"
```

And run migrations

``` bash
php artisan migrate
```


### My own Openpay Exceptions Handler 

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
[ico-travis]: https://img.shields.io/travis/perafan/cashier-openpay/master.svg?style=flat-square
[ico-styleci]: https://styleci.io/repos/12345678/shield

[link-packagist]: https://packagist.org/packages/perafan/cashier-openpay
[link-downloads]: https://packagist.org/packages/perafan/cashier-openpay
[link-travis]: https://travis-ci.org/perafan/cashier-openpay
[link-styleci]: https://styleci.io/repos/12345678
[link-author]: https://github.com/perafan18
