<?php
namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PaymentController extends Controller
{
    public function pay($id)
    {
        $booking = Booking::find($id);

        if (!$booking) {
            return redirect()->back()->with('error', 'رزرو پیدا نشد.');
        }

        if ($booking->status === 'paid') {
            return redirect()->route('filament.admin.resources.bookings.index')
                ->with('message', 'این رزرو قبلاً پرداخت شده است.');
        }

        $amount = $booking->service->price ?? 0; // اگه relation درست نیست صفر می‌شه

        // نمونه ساده درخواست به زرین‌پال
        $response = Http::post('https://api.zarinpal.com/pg/v4/payment/request.json', [
            'merchant_id' => env('ZARINPAL_MERCHANT_ID'),
            'amount' => $amount,
            'callback_url' => route('booking.payment.callback', $booking->id),
            'description' => "پرداخت رزرو #{$booking->id}",
        ]);

        $result = $response->json();

        if (isset($result['data']['code']) && $result['data']['code'] == 100) {
            return redirect("https://www.zarinpal.com/pg/StartPay/{$result['data']['authority']}");
        }

        return redirect()->back()->with('error', 'خطا در ایجاد پرداخت.');
    }

    public function callback($id, Request $request)
    {
        $booking = Booking::find($id);
        $status = $request->get('Status');
        $authority = $request->get('Authority');

        if ($status == 'OK') {
            $booking->update(['status' => 'paid']);
            return redirect()->route('filament.admin.resources.bookings.index')
                ->with('message', 'پرداخت با موفقیت انجام شد!');
        }

        return redirect()->route('filament.admin.resources.bookings.index')
            ->with('error', 'پرداخت انجام نشد.');
    }
}
