<?php

namespace Perafan\CashierOpenpay\Tests;

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Orchestra\Testbench\TestCase;
use Perafan\CashierOpenpay\CashierOpenpayServiceProvider;
use Perafan\CashierOpenpay\Openpay\Plan as OpenpayPlan;
use Perafan\CashierOpenpay\Tests\Database\Migrations\CreateUsersTable;
use Perafan\CashierOpenpay\Tests\Fixtures\User;

abstract class BaseTestCase extends TestCase
{
    /**
     * @param Application $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [CashierOpenpayServiceProvider::class];
    }

    /**
     * Execute table migrations.
     * @return BaseTestCase
     */
    protected function withPackageMigrations()
    {
        $migrations_dir = __DIR__.'/../database/migrations';

        $this->runMigrations(
            collect(
                [
                    [
                        'class' => CreateUsersTable::class,
                        'file_path' => __DIR__.'/database/migrations/create_users_table.php',
                    ],
                    [
                        'class' => '\CreateCustomerColumns',
                        'file_path' => $migrations_dir.'/create_customer_columns.php.stub',
                    ],
                    [
                        'class' => '\CreateSubscriptionsTable',
                        'file_path' => $migrations_dir.'/create_subscriptions_table.php.stub',
                    ],
                    [
                        'class' => '\CreateCardsTable',
                        'file_path' => $migrations_dir.'/create_cards_table.php.stub',
                    ],
                    [
                        'class' => '\CreateBankAccountsTable',
                        'file_path' => $migrations_dir.'/create_bank_accounts_table.php.stub',
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
        include_once __DIR__.'/../vendor/openpay/sdk/data/OpenpayApiError.php';
    }

    /**
     * @param string $url
     * @param string $method
     * @param array $response
     * @return Request
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
            $url,
            $method,
            [],
            [],
            [],
            [],
            json_encode($response)
        );
    }

    /**
     * @return int
     */
    protected function randomExternalId()
    {
        return rand(1000, 10000);
    }

    /**
     * @param array $options
     * @return User
     */
    protected function createUser(array $options = [])
    {
        return User::create(array_merge([
            'email' => 'email@cashier-test.com',
            'name' => 'Taylor Otwell',
            'password' => Hash::make('HelloCashier123'),
        ], $options));
    }

    /**
     * @param array $options
     * @return OpenpayPlan
     */
    protected function createPlan(array $options = [])
    {
        return OpenpayPlan::add(array_merge([
            'amount' => 150.00,
            'status_after_retry' => 'cancelled',
            'retry_times' => 2,
            'name' => 'Plan 1',
            'repeat_unit' => 'month',
            'trial_days' => '30',
            'repeat_every' => '1',
            'currency' => 'MXN'
        ], $options));
    }

    /**
     * @param array $options
     * @return array
     */
    protected function cardData(array $options = [])
    {
        return array_merge([
            'holder_name' => 'Taylor Otwell',
            'card_number' => '4111111111111111',
            'cvv2' => '123',
            'expiration_month' => '12',
            'expiration_year' => '30',
        ], $options);
    }

    /**
     * @param array $options
     * @return array
     */
    protected function addressData(array $options = [])
    {
        return array_merge([
            'line1' => 'Avenida Carranza 1115',
            'postal_code' => '78230',
            'state' => 'San Luis Potosí',
            'city' => 'San Luis Potosí',
            'country_code' => 'MX'
        ], $options);
    }
}
