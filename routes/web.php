<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\CustomerBookingController;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/customer-booking', [CustomerBookingController::class, 'create'])->name('customer.booking.create');
Route::post('/customer-booking', [CustomerBookingController::class, 'store'])->name('customer.booking.store');
Route::get('/slots', [CustomerBookingController::class, 'getSlots']);
Route::get('/service-price', [CustomerBookingController::class, 'getServicePrice']);
Route::get('/payment/callback/{booking}', [CustomerBookingController::class, 'callback'])
    ->name('payment.callback');
