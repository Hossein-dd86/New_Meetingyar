<?php

namespace App\Filament\Resources\Bookings\Pages;

use App\Filament\Resources\Bookings\BookingResource;
use Filament\Resources\Pages\CreateRecord;
use App\Models\User;
use App\Services\SmsService;

class CreateBooking extends CreateRecord
{
    protected static string $resource = BookingResource::class;
    protected static ?string $title = 'رزرواسیون';

    protected function mutateFormDataBeforeCreate( $data): array
    {
        // ابتدا کاربر جدید بساز
        $user = User::create([
            'name' => $data['name'],
            'phone' => $data['phone'],
            'email' => $data['email'],
            'password' => $data['password'], // قبلا bcrypt شده
        ]);


        // user_id را به داده‌های Booking اضافه کن
        $data['user_id'] = $user->id;
        $data['status'] = 'unpaid';
        return $data;


    }
    protected function afterCreate(): void
    {
        $booking = $this->record;

        if (!$booking) return;

        // --- ارسال پیامک به آرایشگر ---
        $barber = \App\Models\User::find($booking->barber_id);
        if ($barber && $barber->phone) {
            $phone = $barber->phone;
            $text = "رزرو جدید ثبت شد. مشتری: {$booking->name}, شماره: {$booking->phone}, تاریخ: {$booking->date}, ساعت: {$booking->start_time}";

            $username = config('sms.username');
            $password = config('sms.password');
            $from = config('sms.from');

            $url = "https://media.sms24.ir/SMSInOutBox/SendSms?username={$username}&password={$password}&from={$from}&to={$phone}&text=" . urlencode($text);

            try {
                $result = file_get_contents($url);
                \Log::info("SMS sent to barber ({$phone}): " . $result);
            } catch (\Exception $e) {
                \Log::error("SMS sending to barber failed: " . $e->getMessage());
            }
        } else {
            \Log::warning("Barber not found or phone empty for booking ID: {$booking->id}");
        }

        // --- ارسال پیامک به کاربر رزرو کننده ---
        $user = $booking->user; // رابطه user باید تو مدل Booking تعریف شده باشد
        if ($user && $user->phone) {
            $phone = $user->phone;
            $text = "رزرو شما ثبت شد. آرایشگر: {$barber->name}, تاریخ: {$booking->date}, ساعت: {$booking->start_time}";

            $url = "https://media.sms24.ir/SMSInOutBox/SendSms?username={$username}&password={$password}&from={$from}&to={$phone}&text=" . urlencode($text);

            try {
                $result = file_get_contents($url);
                \Log::info("SMS sent to user ({$phone}): " . $result);
            } catch (\Exception $e) {
                \Log::error("SMS sending to user failed: " . $e->getMessage());
            }
        } else {
            \Log::warning("User not found or phone empty for booking ID: {$booking->id}");
        }
    }

}
