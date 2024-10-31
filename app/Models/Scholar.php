<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\User;

class Scholar extends User
{
    use HasApiTokens, Notifiable;

    protected $table = 'scholars';

    protected $primaryKey = 'scholar_id';

    protected $fillable = [
        'firstname',
        'lastname',
        'age',
        'address',
        'mobilenumber',
        'scholar_type_id',
        'user_id',
        'school_id',
        'baranggay_id'
    ];

    public function eventType() {
        return $this->hasOne('App\Models\EventType', 'event_type_id');
    }

    public function user() {
        return $this->belongsTo('App\Models\User','user_id');
    }

    public function school() {
        return $this->belongsTo('App\Models\School','school_id');
    }

    public function baranggay() {
        return $this->belongsTo('App\Models\Baranggay','baranggay_id');
    }

    public function submissions() {
        return $this->hasMany('App\Models\Submission','submission_id');
    }
}