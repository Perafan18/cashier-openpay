<?php

namespace Perafan\CashierOpenpay\Tests\Traits;

use Exception;
use OpenpayApiAuthError;
use OpenpayApiConnectionError;
use OpenpayApiError;
use OpenpayApiRequestError;
use OpenpayApiTransactionError;
use Throwable;
use Perafan\CashierOpenpay\Tests\BaseTestCase;
use Perafan\CashierOpenpay\Traits\OpenpayExceptionsHandler;

class OpenpayExceptionHandlerTest extends BaseTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->openpayExceptions();
    }

    public function testIsOpenPayExceptionGenericException()
    {
        $result = (new Handler)->isOpenpayException(new Exception);

        $this->assertFalse($result);
    }

    public function testIsOpenPayExceptionApiError()
    {
        $result = (new Handler)->isOpenpayException(new OpenpayApiError);

        $this->assertTrue($result);
    }

    public function testIsOpenPayExceptionApiAuthError()
    {
        $result = (new Handler)->isOpenpayException(new OpenpayApiAuthError);

        $this->assertTrue($result);
    }

    public function testIsOpenPayExceptionApiTransactionError()
    {
        $result = (new Handler)->isOpenpayException(new OpenpayApiTransactionError);

        $this->assertTrue($result);
    }

    public function testIsOpenPayExceptionApiConnectionError()
    {
        $result = (new Handler)->isOpenpayException(new OpenpayApiConnectionError);

        $this->assertTrue($result);
    }

    public function testRenderWithGenericException()
    {
        $request = $this->request();
        $message = 'Error';

        try {
            throw new Exception($message);
        } catch (Exception $exception) {
            $response = (new Handler)->render($request, $exception);
        }

        $this->assertEquals(json_encode(['message' => $message]), $response->getContent());
    }

    public function testRenderJsonAjaxRequestWithApiAuthError()
    {
        $request = $this->ajaxRequest();

        $request_id = 1;
        $exception_message = 'Error';
        $message = __('Internal server error, contact support');
        $category = 'request';
        $error_code = 1000;
        $http_code = 404;
        $fraud_rules = [];

        $expected_response_data = $this->expectedResponseJson($message, $exception_message, $error_code, $category, $request_id, $http_code, $fraud_rules);

        try {
            throw new OpenpayApiAuthError($exception_message, $error_code, $category, $request_id, $http_code, $fraud_rules);
        } catch (Exception $exception) {
            $response = (new Handler)->render($request, $exception);
        }

        $this->assertEquals($expected_response_data, $response->getContent());
        $this->assertEquals($http_code, $response->getStatusCode());
    }

    public function testRenderJsonAjaxRequestWithApiTransactionError()
    {
        $request = $this->ajaxRequest();

        $request_id = 1;
        $exception_message = 'The card number verification digit is invalid';
        $message = __('The card number verification digit is invalid');
        $category = 'request';
        $error_code = 2004;
        $http_code = 422;
        $fraud_rules = [];

        $expected_response_data = $this->expectedResponseJson($message, $exception_message, $error_code, $category, $request_id, $http_code, $fraud_rules);

        try {
            throw new OpenpayApiTransactionError($exception_message, $error_code, $category, $request_id, $http_code, $fraud_rules);
        } catch (Exception $exception) {
            $response = (new Handler)->render($request, $exception);
        }

        $this->assertEquals($expected_response_data, $response->getContent());
        $this->assertEquals($http_code, $response->getStatusCode());
    }

    public function testRenderJsonAjaxRequestWithApiRequestError()
    {
        $request = $this->ajaxRequest();

        $request_id = 1;
        $exception_message = 'The card number verification digit is invalid';
        $message = __('The card number verification digit is invalid');
        $category = 'request';
        $error_code = 2004;
        $http_code = 422;
        $fraud_rules = [];

        $expected_response_data = $this->expectedResponseJson($message, $exception_message, $error_code, $category, $request_id, $http_code, $fraud_rules);

        try {
            throw new OpenpayApiRequestError($exception_message, $error_code, $category, $request_id, $http_code, $fraud_rules);
        } catch (Exception $exception) {
            $response = (new Handler)->render($request, $exception);
        }

        $this->assertEquals($expected_response_data, $response->getContent());

        $this->assertEquals($http_code, $response->getStatusCode());
    }

    public function testRenderBackWithApiTransactionError()
    {
        $request = $this->request();
        $request_id = 1;
        $exception_message = 'Error';
        $message = __('The card number verification digit is invalid');
        $category = 'request';
        $error_code = 2004;
        $http_code = 422;
        $fraud_rules = [];

        $expected_response_data = $this->expectedResponseHtml();

        try {
            throw new OpenpayApiTransactionError($exception_message, $error_code, $category, $request_id, $http_code, $fraud_rules);
        } catch (Exception $exception) {
            $response = (new Handler)->render($request, $exception);
        }

        $this->assertEquals($expected_response_data, $response->getContent());
        $this->assertEquals([$request_id], $response->getSession()->get('errors')->get('openpay_error_request_id'));
        $this->assertEquals([$message], $response->getSession()->get('errors')->get('openpay_error_message'));
        $this->assertEquals([$exception_message], $response->getSession()->get('errors')->get('openpay_error_message_original'));
        $this->assertEquals([$http_code], $response->getSession()->get('errors')->get('openpay_error_http_code'));
        $this->assertEquals([$category], $response->getSession()->get('errors')->get('openpay_error_category'));
        $this->assertEquals([$error_code], $response->getSession()->get('errors')->get('openpay_error_code'));
        $this->assertEquals(302, $response->getStatusCode());
    }

    public function testRenderBackWithUnknownOpenpayError()
    {
        $request = $this->request();
        $request_id = 1;
        $exception_message = 'Error';
        $message = 'Error';
        $category = 'request';
        $error_code = 8000;
        $http_code = 500;
        $fraud_rules = [];

        $expected_response_data = $this->expectedResponseHtml();

        try {
            throw new OpenpayApiTransactionError($exception_message, $error_code, $category, $request_id, $http_code, $fraud_rules);
        } catch (Exception $exception) {
            $response = (new Handler)->render($request, $exception);
        }

        $this->assertEquals($expected_response_data, $response->getContent());
        $this->assertEquals([$request_id], $response->getSession()->get('errors')->get('openpay_error_request_id'));
        $this->assertEquals([$message], $response->getSession()->get('errors')->get('openpay_error_message'));
        $this->assertEquals([$exception_message], $response->getSession()->get('errors')->get('openpay_error_message_original'));
        $this->assertEquals([$http_code], $response->getSession()->get('errors')->get('openpay_error_http_code'));
        $this->assertEquals([$category], $response->getSession()->get('errors')->get('openpay_error_category'));
        $this->assertEquals([$error_code], $response->getSession()->get('errors')->get('openpay_error_code'));
        $this->assertEquals(302, $response->getStatusCode());
    }

    private function expectedResponseJson($message, $original_message, $code, $category, $request_id, $http_code, $fraud_rules)
    {
        return json_encode([
            'openpay_error_request_id' => $request_id,
            'openpay_error_message' => $message,
            'openpay_error_message_original' => $original_message,
            'openpay_error_http_code' => $http_code,
            'openpay_error_category' => $category,
            'openpay_error_code' => $code,
        ]);
    }

    private function expectedResponseHtml()
    {
        $expected_response_data = file_get_contents(__DIR__.'/../Fixtures/redirect_localhost.html');

        return substr($expected_response_data, 0, -1);
    }
}

class Handler
{
    use OpenpayExceptionsHandler;

    /**
     * @param $request
     * @param Throwable $exception
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function render($request, Throwable $exception)
    {
        if ($this->isOpenpayException($exception)) {
            return $this->renderOpenpayException($request, $exception);
        }

        return response()->json(['message' => $exception->getMessage()]);
    }
}
