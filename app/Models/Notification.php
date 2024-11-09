<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasApiTokens, Notifiable;

    protected $table = 'notifications';
    protected $primaryKey = 'notification_id';

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
