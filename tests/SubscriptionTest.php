<?php

namespace Perafan\CashierOpenpay\Tests;

use Carbon\Carbon;
use Perafan\CashierOpenpay\Openpay\Plan;
use Perafan\CashierOpenpay\Tests\Fixtures\User;

class SubscriptionTest extends BaseTestCase
{
    /**
     * @var User
     */
    protected static $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->withPackageMigrations();
        self::$user = $this->createUser();

        self::$user->createAsOpenpayCustomer([
            'external_id' => $this->randomExternalId(),
        ]);
    }

    public function tearDown(): void
    {
        self::$user->asOpenpayCustomer()->delete();

        parent::tearDown();
    }

    /** @test */
    public function testOnGracePeriod()
    {
        $plan = $this->createPlan();

        $subscription_data = [
            'card' => $this->cardData(),
        ];

        $subscription = self::$user->newSubscription($plan->id, $subscription_data);

        $this->assertTrue($subscription->hasPlan($plan->id));

        $this->assertFalse($subscription->onGracePeriod());

        $this->assertTrue($subscription->onTrial());

        $this->assertFalse($subscription->pastDue());

        $this->assertFalse($subscription->unpaid());

        $this->assertFalse($subscription->ended());

        $this->assertEquals(self::$user->id, $subscription->owner->id);

        $this->assertFalse($subscription->cancelled());

        $subscription->cancel();

        $this->assertTrue($subscription->cancelled());

        $this->assertTrue($subscription->onGracePeriod());

        $subscription->cancelNow();

        Plan::delete($plan->id);
    }

    /** @test */
    public function testOnGracePeriods()
    {
        $plan = $this->createPlan();

        $subscription_data = [
            'card' => $this->cardData(),
            'trial_end_date' => Carbon::now()->subDay(),
        ];

        $subscription = self::$user->newSubscription($plan->id, $subscription_data);

        $this->assertTrue($subscription->hasPlan($plan->id));

        $this->assertFalse($subscription->onGracePeriod());

        $this->assertFalse($subscription->onTrial());

        $this->assertFalse($subscription->pastDue());

        $this->assertFalse($subscription->unpaid());

        $this->assertFalse($subscription->ended());

        $this->assertEquals(self::$user->id, $subscription->owner->id);

        $this->assertFalse($subscription->cancelled());

        $subscription->cancelNow();

        $this->assertFalse($subscription->onGracePeriod());

        $this->assertTrue($subscription->cancelled());

        Plan::delete($plan->id);
    }
}
