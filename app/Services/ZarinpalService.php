<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ZarinpalService
{
    public static function requestPayment($amount, $callbackUrl, $mobile = null, $email = null)
    {
        $merchantId = config('services.zarinpal.merchant_id');

        $response = Http::post('https://api.zarinpal.com/pg/v4/payment/request.json', [
            'merchant_id' => $merchantId,
            'amount' => $amount,
            'callback_url' => $callbackUrl,
            'description' => 'پرداخت رزرو',
            'metadata' => [
                'mobile' => $mobile,
                'email' => $email,
            ],
        ]);

        $result = $response->json();

        // اگر درخواست موفق بود، کد وضعیت 100 دریافت می‌کنیم
        if (isset($result['data']['authority'])) {
            // لینک پرداخت (StartPay)
            $authority = $result['data']['authority'];

            return "https://www.zarinpal.com/pg/StartPay/$authority";
        }

        return null;
    }

    public static function verifyPayment($authority, $amount)
    {
        $merchantId = config('services.zarinpal.merchant_id');

        $response = Http::post('https://api.zarinpal.com/pg/v4/payment/verify.json', [
            'merchant_id' => $merchantId,
            'authority' => $authority,
            'amount' => $amount,
        ]);

        return $response->json();
    }
}
