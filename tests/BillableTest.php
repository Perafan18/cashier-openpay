<?php

namespace Perafan\CashierOpenpay\Tests;

use Carbon\Carbon;
use Perafan\CashierOpenpay\Openpay\Plan as OpenpayPlan;
use Perafan\CashierOpenpay\Subscription;

class BillableTest extends BaseTestCase
{
    protected static $plan;

    protected static $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->withPackageMigrations();

        self::$user = $this->createUser();

        self::$user->createAsOpenpayCustomer([
            'external_id' => $this->randomExternalId(),
        ]);

        self::$plan = $this->createPlan();
    }

    public function tearDown(): void
    {
        self::$user->asOpenpayCustomer()->delete();

        OpenpayPlan::delete(self::$plan->id);

        parent::tearDown();
    }

    /** @test */
    public function testOpenpayId()
    {
        $user = $this->createUser(['email' => 'random@email.com']);

        $this->assertFalse($user->hasOpenpayId());
    }

    /** @test */
    public function testCreateOpenpayCustomer()
    {
        $user = $this->createUser(['name' => 'Pedro Perafán', 'email' => 'random@email.com']);

        $external_id = $this->randomExternalId();

        $this->assertFalse($user->hasOpenpayId());

        $user->createAsOpenpayCustomer([
            'external_id' => $external_id,
        ]);

        $this->assertTrue($user->hasOpenpayId());

        $customer = $user->asOpenpayCustomer();

        $this->assertIsObject($customer);

        $this->assertEquals('Pedro Perafán', $customer->name);

        $this->assertEquals($external_id, $customer->external_id);

        $user->asOpenpayCustomer()->delete();
    }

    /** @test */
    public function testNewCard()
    {
        $card = self::$user->addCard($this->cardData(), $this->addressData());

        $this->assertEquals('411111XXXXXX1111', $card->card_number);

        $openpayCard = $card->asOpenpayCard();

        $this->assertTrue($openpayCard->allows_charges);

        $this->assertTrue($openpayCard->allows_payouts);
    }

    /** @test */
    public function testCharge()
    {
        $options = [
            'method' => 'bank_account',
            'description' => 'Cargo con banco',
        ];

        $charge = self::$user->charge(100.00, $options);

        $this->assertEquals('in_progress', $charge->status);
        $this->assertEquals('charge', $charge->transaction_type);
        $this->assertEquals('bank_transfer', $charge->payment_method->type);
    }

    /** @test */
    public function testNewSubscription()
    {
        $card_data = $this->cardData();

        $address = $this->addressData();

        $card = self::$user->addCard($card_data, $address);

        $openpayCard = $card->asOpenpayCard();

        $subscription = self::$user->newSubscription(self::$plan->id, ['source_id' => $openpayCard->id]);

        $this->assertEquals(self::$plan->id, $subscription->openpay_plan);

        $this->assertEquals(Subscription::STATUS_TRIAL, $subscription->openpay_status);

        $this->assertTrue(self::$user->onTrial('default'));

        $this->assertFalse(self::$user->onTrial('test'));

        $subscription->cancelNow();
    }

    /** @test */
    public function HasSubscription()
    {
        $subscription_data = [
            'card' => $this->cardData(),
            'trial_end_date' => Carbon::now()->subDay(),
        ];

        $subscription = self::$user->newSubscription(self::$plan->id, $subscription_data, 'test');

        $this->assertEquals(Subscription::STATUS_ACTIVE, $subscription->openpay_status);

        $this->assertEquals($subscription->id, self::$user->subscription('test')->id);

        $this->assertFalse(self::$user->onTrial('test'));

        $this->assertTrue(self::$user->subscribed('test'));

        $this->assertTrue(self::$user->subscribed('test', self::$plan->id));

        $this->assertFalse(self::$user->subscribed('hello', self::$plan->id));

        $this->assertFalse(self::$user->subscribed('hello'));

        $subscription->cancelNow();
    }

    /** @test */
    public function checkIfBillableModel()
    {
        $subscription_data = [
            'card' => $this->cardData(),
            'trial_end_date' => Carbon::now()->subDay(),
        ];

        $subscription = self::$user->newSubscription(self::$plan->id, $subscription_data);

        $this->assertTrue(self::$user->subscribedToPlan(self::$plan->id, 'default'));

        $this->assertTrue(self::$user->onPlan(self::$plan->id));

        $subscription->cancelNow();
    }
}
