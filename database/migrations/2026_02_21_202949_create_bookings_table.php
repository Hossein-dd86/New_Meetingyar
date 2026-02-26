<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users'); // مشتری
            $table->foreignId('barber_id')->constrained('users'); // آرایشگر
            $table->foreignId('service_id')->constrained('services'); // سرویس انتخاب شده
            $table->string('name', 50);
            $table->string('phone', 12);
            $table->string('password');
            $table->date('date'); // تاریخ رزرو
            $table->string('start_time'); // ساعت انتخابی
            $table->enum('status', [
                'pending',     // ایجاد شده ولی هنوز وارد پرداخت نشده
                'unpaid',      // در انتظار پرداخت
                'paid',        // پرداخت موفق
                'failed',      // پرداخت ناموفق
                'cancelled',   // لغو شده توسط کاربر یا ادمین
                'expired',     // منقضی شده (عدم پرداخت در زمان مشخص)
                'refunded'     // مبلغ برگشت داده شده
            ])->default('pending')->comment('pending      ایجاد شده ولی هنوز وارد پرداخت نشده
unpaid       در انتظار پرداخت
paid         پرداخت موفق
failed       پرداخت ناموفق
cancelled    لغو شده توسط کاربر یا ادمین
expired      منقضی شده (عدم پرداخت در زمان مشخص)
refunded     مبلغ برگشت داده شده');
            $table->string('ref_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
