<?php

namespace Perafan\CashierOpenpay\Tests\Http\Controllers;

use Perafan\CashierOpenpay\Http\Controllers\BaseWebhookController;
use Perafan\CashierOpenpay\Tests\BaseTestCase;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class WebhookControllerTest extends BaseTestCase
{
    public function testChargeSucceededAndMethodExists()
    {
        $request = $this->request('/','POST',['type' => 'charge.succeeded']);

        $response = (new WebhookControllerTestStub)->handleWebhook($request);

        $this->assertEquals('Webhook Charge Succeeded', $response->getContent());

        $this->assertEquals(200,  $response->getStatusCode());
    }

    public function testVerificationAndMethodExists()
    {
        $request = $this->request('/','POST',['type' => 'verification']);

        $response = (new WebhookControllerTestStub)->handleWebhook($request);

        $this->assertEquals('Webhook Verification', $response->getContent());

        $this->assertEquals(200,  $response->getStatusCode());
    }

    public function testRandomEventAndTheMethodDoesntExists()
    {
        $request = $this->request('/','POST',['type' => 'random.event']);

        $response = (new WebhookControllerTestStub)->handleWebhook($request);

        $this->assertEquals('', $response->getContent());
    }
}

class WebhookControllerTestStub extends BaseWebhookController
{
    protected function handleChargeSucceeded()
    {
        return new Response('Webhook Charge Succeeded', 200);
    }

    protected function handleVerification()
    {
        return new Response('Webhook Verification', 200);
    }
}
