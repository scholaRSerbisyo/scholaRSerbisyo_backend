<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use App\Models\Scholar;

class Notification extends Model
{
    use HasApiTokens, Notifiable;

    protected $table = 'notifications';
    protected $primaryKey = 'notification_id';

    protected $fillable = [
        'event_id',
        'event_name',
        'event_type_name',
        'description',
        'event_image_uuid',
    ];

    public function event()
    {
        return $this->belongsTo('App\Models\Event', 'event_id');
    }

    public function scholars() 
    {
        return $this->hasMany(Scholar::class);
    }
}
