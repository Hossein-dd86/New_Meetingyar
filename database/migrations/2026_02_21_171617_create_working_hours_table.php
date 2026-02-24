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
        Schema::create('working_hours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barber_id')->constrained('users'); // آرایشگر مرتبط
            $table->enum('day', ['1','2','3','4','5','6','7'])->comment('1: شنبه     2: یکشنبه     3: دوشنبه     4: سه شنبه     5: چهارشنبه     6: پنجشنبه     7: جمعه');
            $table->time('start_time');
            $table->time('end_time');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('working_hours');
    }
};
