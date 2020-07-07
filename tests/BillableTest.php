<?php

namespace Perafan\CashierOpenpay\Tests;

use Perafan\CashierOpenpay\Tests\Fixtures\User;
use Illuminate\Support\Facades\Hash;

class BillableTest extends BaseTestCase
{
    public function testOpenpayId()
    {
        $this->withPackageMigrations();

        $user = $this->createUser();

        $this->assertFalse($user->hasOpenpayId());
    }

    public function testCreateOpenpayCustomer()
    {
        $this->withPackageMigrations();

        $user = $this->createUser();

        $this->assertFalse($user->hasOpenpayId());

        $user->createAsOpenpayCustomer([
            'external_id' => rand(1000,10000)
        ]);

        $this->assertTrue($user->hasOpenpayId());

        $this->assertIsObject($user->asOpenpayCustomer());

        $user->asOpenpayCustomer()->delete();
    }

    protected function createUser(array $options = [])
    {
        return User::create(array_merge([
            'email' => 'email@cashier-test.com',
            'name' => 'Taylor Otwell',
            'password' => Hash::make('HelloCashier123'),
        ], $options));
    }
}
