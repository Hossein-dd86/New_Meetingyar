<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Service;
use App\Models\User;
use App\Models\WorkingHour;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

class CustomerBookingController extends Controller
{
    public function create(Request $request)
    {
//        $services = Service::all();
//
//        $slots = [];
//        $date = $request->input('date');
//        $serviceId = $request->input('service_id');
//
//        if ($date && $serviceId) {
//            $service = Service::find($serviceId);
//            $duration = $service->time; // دقیقه
//
//            $dayOfWeek = Carbon::parse($date)->dayOfWeekIso;
//
//            $workingHour = WorkingHour::where('barber_id', auth()->id())
//                ->where('day', $dayOfWeek)
//                ->first();
//
//            if ($workingHour) {
//                $start = Carbon::parse($workingHour->start_time);
//                $end = Carbon::parse($workingHour->end_time);
//                $current = $start->copy();
//
//                while ($current->lte($end->copy()->subMinutes($duration))) {
//                    $slotEnd = $current->copy()->addMinutes($duration);
//                    $label = $current->format('H:i') . ' - ' . $slotEnd->format('H:i');
//                    $slots[$label] = $current->format('H:i'); // فقط زمان شروع ذخیره میشه
//                    $current->addMinutes($duration);
//                }
//            }
//        }
        $now_barber = 1;
        $Services = Service::where('barber_id', $now_barber)->get();
        $price = Service::where('price', $now_barber)->get();
        return view('customer_booking.create', compact('now_barber', 'Services'));
    }
    public function getSlots(Request $request)
    {
        $date = $request->input('date');
        $barberId = 1; // فرضی، یا می‌توان از فرم گرفت
        $slots = [];

        if ($date) {
            $dayOfWeek = Carbon::parse($date)->dayOfWeekIso;

            $workingHour = WorkingHour::where('barber_id', $barberId)
                ->where('day', $dayOfWeek)
                ->first();

            if ($workingHour) {
                $start = Carbon::parse($workingHour->start_time);
                $end = Carbon::parse($workingHour->end_time);
                $current = $start->copy();

                // گرفتن رزروهای موجود
                $existingBookings = Booking::where('barber_id', $barberId)
                    ->where('date', $date)
                    ->pluck('start_time') // فرض کنیم start_time با H:i ذخیره شده
                    ->toArray();

                while ($current->lte($end)) {
                    $slot = $current->format('H:i');
                    $slots[] = [
                        'time' => $slot,
                        'available' => !in_array($slot, $existingBookings) // true اگر رزرو نشده
                    ];
                    $current->addMinutes(60); // طول هر اسلات
                }
            }
        }

        return response()->json($slots);
    }

    public function getServicePrice(Request $request)
    {
        $serviceId = $request->input('service_id');
        $price = 0;

        if ($serviceId) {
            $service = Service::find($serviceId);
            if ($service) {
                $price = $service->price;
            }
        }

        return response()->json(['price' => $price]);
    }

    public function store(Request $request)
    {

        // اعتبارسنجی فرم
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'password' => 'required|string|min:6',
            'service_id' => 'required|exists:services,id',
            'date' => 'required|date',
            'start_time' => 'required|string',
        ]);

        // 1️⃣ بررسی وجود کاربر
        $user = User::where('email', $request->email)
            ->orWhere('phone', $request->phone)
            ->first();

        // 2️⃣ اگر کاربر وجود نداشت، بسازیم
        if (!$user) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'role' => 'user', // فرضاً نقش مشتری
            ]);
        }

        $userId = $user->id;

        // 3️⃣ ثبت رزرو
        $booking = Booking::create([
            'name' => "test",
            'phone' => $request->phone,
            'password' => "sssss",
            'user_id' => $userId,
            'barber_id' => $request->barber_id ?? 1, // اگر ثابت هست
            'service_id' => $request->service_id,
            'date' => $request->date,
            'start_time' => $request->start_time,
        ]);


        $service = Service::find($request->service_id);
        $amount = $service->price;

        // 1️⃣ درخواست ایجاد تراکنش به زرین‌پال
        $response = Http::post('https://api.zarinpal.com/pg/v4/payment/request.json', [
            "merchant_id" => env('ZARINPAL_MERCHANT_ID'),
            "amount" => 30000,
            "description" => "رزرو نوبت",
            "callback_url" => route('payment.callback', $booking->id),
        ]);

        $result = $response->json();

        if (isset($result['data']) && in_array($result['data']['code'], [100, 101])) {

            $booking->status = 'paid';
            $booking->ref_id = $result['data']['ref_id'] ?? null;
            $booking->save();

            return redirect()->route('welcome', $booking->id);

        } else {

            \Log::error('Zarinpal Verify Error', $result);

            return redirect()->route('customer.booking.cancel', $booking->id)
                ->with('error', 'پرداخت تایید نشد');
        }
        return redirect()->back()->with('success', 'رزرو شما ثبت شد!');

    }
    public function callback(Request $request, $booking_id)
    {
        $booking = Booking::findOrFail($booking_id);

        if($request->Status == 'OK') {
            // تایید تراکنش
            $response = Http::get("https://www.zarinpal.com/pg/rest/WebGate/PaymentVerification.json", [
                'MerchantID' => env('ZARINPAL_MERCHANT_ID'),
                'Authority' => $booking->transaction_id,
                'Amount' => $booking->service->price,
            ]);

            $result = $response->json();

            if($result['Status'] == 100) {
                $booking->status = 'paid';
                $booking->ref_id = $result['RefID']; // شماره رسید زرین‌پال
                $booking->save();

                return redirect()->route('customer.booking.success', $booking->id)
                    ->with('success', 'پرداخت با موفقیت انجام شد!');
            }
        }

        return redirect()->route('customer.booking.cancel', $booking->id)
            ->with('error', 'پرداخت انجام نشد!');
    }
}
