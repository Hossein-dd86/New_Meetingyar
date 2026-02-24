<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $table= "services";
    protected $fillable = [
        'name',
        'description',
        'price',
        'time',
        'barber_id'
    ];
    public function barber()
{
    return $this->belongsTo(\App\Models\User::class, 'barber_id');
}
}
