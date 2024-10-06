<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\User;

class Scholar extends User
{
    use HasApiTokens, Notifiable;

    protected $table = 'scholars';

    protected $fillable = [
        'firstname',
        'lastname',
        'age',
        'address',
        'mobilenumber'
    ];

    public function eventType() {
        return $this->hasOne('App\Models\EventType', 'event_type_id');
    }

    public function user() {
        return $this->belongsTo('App\Models\User','user_id');
    }
}