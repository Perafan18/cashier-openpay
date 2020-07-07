<?php

namespace Perafan\CashierOpenpay\Tests;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Laravel\Cashier\Tests\Database\Migrations\CreateUsersTable;
use Orchestra\Testbench\TestCase;
use Perafan\CashierOpenpay\CashierOpenpayServiceProvider;

abstract class BaseTestCase extends TestCase
{
    /**
     * @param \Illuminate\Foundation\Application $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [CashierOpenpayServiceProvider::class];
    }

    /**
     * Execute table migrations.
     * @return \Laravel\Cashier\Tests\BaseTestCase
     */
    protected function withPackageMigrations()
    {
        $migrations_dir = __DIR__.'/../database/migrations';

        $this->runMigrations(
            collect(
                [
                    [
                        'class' => CreateUsersTable::class,
                        'file_path' => __DIR__ . '/database/migrations/create_users_table.php',
                    ],
                    [
                        'class' => '\CreateCustomerColumns',
                        'file_path' => $migrations_dir . '/create_customer_columns.php.stub',
                    ],
                    [
                        'class' => '\CreateSubscriptionsTable',
                        'file_path' => $migrations_dir . '/create_subscriptions_table.php.stub',
                    ],
                ]
            )
        );

        return $this;
    }

    /**
     * Runs a collection of migrations.
     *
     * @param Collection $migrations
     */
    protected function runMigrations(Collection $migrations)
    {
        $migrations->each(function ($migration) {
            $this->runMigration($migration['class'], $migration['file_path']);
        });
    }

    /**
     * @param string $class
     * @param string $file_path
     */
    protected function runMigration($class, $file_path)
    {
        include_once $file_path;
        (new $class)->up();
    }

    protected function openpayExceptions()
    {
        include_once __DIR__ .'/../vendor/openpay/sdk/data/OpenpayApiError.php';
    }

    /**
     * @param string $url
     * @param string $method
     * @param array $response
     */
    protected function ajaxRequest($url = '/', $method = 'POST', $response = [])
    {
        $request = $this->request($url, $method, $response);
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');

        return $request;
    }

    /**
     * @param string $url
     * @param string $method
     * @param array $response
     * @return Request
     */
    protected function request($url = '/', $method = 'POST', $response = [])
    {
        return Request::create(
            $url, $method, [], [], [], [], json_encode($response)
        );
    }
}
