<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\ZarinpalService;

class Booking extends Model
{
    protected $table = "bookings";
    protected $fillable = [
        'barber_id',   // اضافه کن
        'user_id',     // اضافه کن
        'service_id',
        'name',
        'phone',
        'password',
        'date',
        'start_time',
        'name',
        'phone',
        'password',
        'status',
    ];
    public function user()
{
    return $this->belongsTo(User::class, 'user_id');
}
    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
