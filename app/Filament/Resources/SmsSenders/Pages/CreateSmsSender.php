<?php

namespace App\Filament\Resources\SmsSenders\Pages;

use App\Filament\Resources\SmsSenders\SmsSenderResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Http;

class CreateSmsSender extends CreateRecord
{
    protected static string $resource = SmsSenderResource::class;
    protected static ?string $title = 'ارسال پیامک';

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        $username = config('sms.username');
        $password = config('sms.password');
        $from     = config('sms.from');

        $phone = $data['phone'];
        $text  = $data['message'];

        $url = "https://media.sms24.ir/SMSInOutBox/SendSms"
            . "?username={$username}"
            . "&password={$password}"
            . "&from={$from}"
            . "&to={$phone}"
            . "&text=" . urlencode($text);

        $response = Http::get($url);

        // برای تست
        // dd($response->body());

        // افزایش شمارنده پیامک کاربر
        $counter = \App\Models\SmsCounter::firstOrCreate(
            ['user_id' => auth()->id()],
            ['count' => 0]
        );

        $counter->increment('count');

        return $counter;
    }
}
