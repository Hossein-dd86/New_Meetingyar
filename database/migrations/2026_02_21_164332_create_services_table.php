<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('نام خدمت'); // نام خدمات
            $table->text('description')->nullable()->comment('توضیحات خدمت'); // توضیحات
            $table->decimal('price', 10, 2)->comment('قیمت خدمت'); // قیمت
            $table->unsignedInteger('time')->comment('زمان مورد نیاز برای انجام خدمت');
            $table->string('is_active')->default('1');
            $table->foreignId('barber_id')
      ->constrained('users')
      ->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
