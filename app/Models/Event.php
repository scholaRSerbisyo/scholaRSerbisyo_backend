<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasApiTokens, Notifiable;

    protected $table = 'events';
    protected $primaryKey = 'event_id';

    protected $fillable = [
        'event_name',
        'description',
        'date',
        'time',
        'location',
        'status',
        'admin',
        'event_type_id',
        'submissions',
        'scholar_id',
        'image_uuid',
        'time_in',
        'time_out'
    ];

    protected $casts = [
        'submissions' => 'array',
    ];

    public function admin()
    {
        return $this->belongsTo('App\Models\AdminType', 'admin_type_id');
    }

    public function eventType() {
        return $this->belongsTo('App\Models\EventType', 'event_type_id');
    }

    public function submissions() {
        return $this->belongsToMany('App\Models\Scholar', 'submissions', 'event_id', 'scholar_id');
    }
}
