<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    protected string $username;
    protected string $password;
    protected string $from;

    public function __construct()
    {
        $this->username = config('sms.username');
        $this->password = config('sms.password');
        $this->from = config('sms.from');
    }

    /**
     * ارسال پیامک
     *
     * @param string $to شماره گیرنده
     * @param string $text متن پیامک
     * @return bool|string پاسخ سرور یا false در صورت خطا
     */
    public function sendSms(string $to, string $text)
    {
        try {
            $response = Http::get('https://media.sms24.ir/SMSInOutBox/SendSms', [
                'username' => $this->username,
                'password' => $this->password,
                'from' => $this->from,
                'to' => $to,
                'text' => $text,
                
            ]);
            
            Log::info('SMS Response: ' . $response->body());
            if ($response->successful()) {
                return $response->body();
            }

            return false;
        } catch (\Exception $e) {
            Log::error('SMS sending error: ' . $e->getMessage());
            return false;
        }
    }
}