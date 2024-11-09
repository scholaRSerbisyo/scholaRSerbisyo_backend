<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasApiTokens, Notifiable;

    protected $table = 'schedules';
    protected $primaryKey = 'schedule_id';

    protected $fillable = [
        'event_id',
        'date'
    ];

    protected $casts = [
        'date' => 'datetime:Y-m-d',
    ];

    public function event()
    {
        return $this->belongsTo('App\Models\Event', 'event_id');
    }

    public function notification() 
    {
        return $this->hasOne('App\Models\Notification', 'notification_id');
    }
}
