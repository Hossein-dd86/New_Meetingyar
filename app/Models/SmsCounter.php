<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Contact;

class SmsCounter extends Model
{
    protected $table = 'sms_counters';
    protected $fillable = ['user_id','count'];
    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }
}
