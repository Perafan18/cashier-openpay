<?php

namespace Perafan\CashierOpenpay\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use OpenpayApiError;

trait OpenpayExceptionsHandler
{

    /**
     * Determine if the given exception is an OpenpayApiError exception.
     *
     * @param $exception
     * @return bool
     */
    public function isOpenpayException($exception)
    {
        return $exception instanceof OpenpayApiError;
    }

    /**
     * Render the given OpenpayApiError.
     *
     * @param \Illuminate\Http\Request $request
     * @param OpenpayApiError $exception
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function renderOpenpayException(Request $request, OpenpayApiError $exception)
    {
        $message = $exception->getMessage();
        $http_error_code = $exception->getHttpCode();
        $error_code = $exception->getErrorCode();

        if ($error_code >= 1000 && $error_code <= 1020) {
            $message = $this->generalErrors($error_code);
        } elseif ($error_code >= 2001 && $error_code <= 2003) {
            $message = $this->storageErrors($error_code);
        } elseif ($error_code >= 2004 && $error_code <= 3205) {
            $message = $this->cardsErrors($error_code);
        } elseif ($error_code >= 4001 && $error_code <= 4002) {
            $message = $this->accountsErrors($error_code);
        } elseif ($error_code >= 6001 && $error_code <= 6003) {
            $message = $this->webhooksErrors($error_code);
        }

        $response_data = [
            'openpay_error_request_id' => $exception->getRequestId(),
            'openpay_error_message' => $message,
            'openpay_error_message_original' => $exception->getMessage(),
            'openpay_error_http_code' => $http_error_code,
            'openpay_error_category' => $exception->getCategory(),
            'openpay_error_code' => $error_code,
        ];

        if (config('cashier_openpay.log_errors')) {
            Log::error('OPENPAY ERROR REQUEST ID: '.$exception->getRequestId());
            Log::error('OPENPAY ERROR MESSAGE: '.$exception->getMessage());
            Log::error('OPENPAY ERROR HTTP CODE: '.$http_error_code);
            Log::error('OPENPAY ERROR CODE: '.$error_code);
            Log::error('OPENPAY ERROR CATEGORY: '.$exception->getCategory());
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($response_data, $http_error_code);
        } else {
            return back()
                ->withInput()
                ->withErrors($response_data, 'cashier');
        }
    }

    protected function generalErrors($error_code)
    {
        switch ($error_code) {
            case 1000:
                return __('Internal server error, contact support');
            case 1001:
                return __('Bad Request');
            case 1002:
                return __('The api key or merchant id are invalid');
            case 1003:
                return __('Parameters look valid but request failed');
            case 1004:
                return __('The resource is unavailable at this moment. Please try again later');
            case 1005:
                return __('The requested resource doesn\'t exist');
            case 1006:
                return __('The order_id has already been processed');
            case 1007:
                return __('Operation rejected by processor');
            case 1008:
                return __('The account is inactive');
            case 1009:
                return __('The request is too large');
            case 1010:
                return __('Method not allowed for public API key, use private key instead');
            case 1011:
                return __('The resource was previously deleted');
            case 1012:
                return __('The transaction amount exceeds your allowed transaction limit');
            case 1013:
                return __('The operation is not allowed on the resource');
            case 1014:
                return __('Your account is inactive, please contact to soporte@openpay.mx for more information');
            case 1015:
                return __('Could not get any response from gateway. Please try again later');
            case 1016:
                return __('The merchant email has been already processed');
            case 1017:
                return __('The payment gateway is not available at the moment, please try again later');
            case 1018:
                return __('The number of retries of charge is greater than allowed');
            case 1020:
                return __('The number of decimal digits is not valid for this currency');
        }
    }

    protected function storageErrors($error_code)
    {
        switch ($error_code) {
            case 2001:
                return __('The bank account already exists');
            case 2003:
                return __('The external_id already exists');
        }
    }

    protected function cardsErrors($error_code)
    {
        switch ($error_code) {
            case 2004:
                return __('The card number verification digit is invalid');
            case 2005:
                return __('The expiration date has expired');
            case 2006:
                return __('The CVV2 security code is required');
            case 2007:
                return __('The card number is only valid in sandbox');
            case 2008:
                return __('The card is not valid for points');
            case 2009:
                return __('The CVV2 security code is invalid');
            case 2010:
                return __('3D Secure authentication failed');
            case 2011:
                return __('Card product type not supported');
            case 3001:
                return __('The card was declined by the bank');
            case 3002:
                return __('The card has expired');
            case 3003:
                return __('The card doesn\'t have sufficient funds');
            case 3004:
                return __('The card was reported as stolen');
            case 3005:
                return __('Fraud risk detected by anti-fraud system');
            case 3006:
                return __('Request not allowed');
            case 3009:
                return __('The card was reported as lost');
            case 3010:
                return __('The bank has restricted the card');
            case 3011:
                return __('The bank has requested the card to be retained');
            case 3012:
                return __('Bank authorization is required for this charge');
            case 3201:
                return __('Merchant not authorized to use payment plan');
            case 3203:
                return __('Invalid promotion for such card type');
            case 3204:
                return __('Transaction amount is less than minimum for promotion');
            case 3205:
                return __('Promotion not allowed');
        }
    }

    protected function accountsErrors($error_code)
    {
        switch ($error_code) {
            case 4001:
                return __('There are not enough funds in the openpay account');
            case 4002:
                return __('The operation can\'t be completed until pending fees are paid');
        }
    }

    protected function webhooksErrors($error_code)
    {
        switch ($error_code) {
            case 6001:
                return __('The webhook has already been processed');
            case 6002:
                return __('Could not connect with webhook service, verify URL');
            case 6003:
                return __('Service responded with an error on this moment. Please try again later');
        }
    }
}

