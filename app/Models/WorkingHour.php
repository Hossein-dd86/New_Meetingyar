<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkingHour extends Model
{
    protected $table = "working_hours";
    protected $fillable = [
        'barber_id',
        'day',
        'start_time',
        'end_time'
    ];
    public function barber()
{
    return $this->belongsTo(User::class, 'barber_id');
}
}
