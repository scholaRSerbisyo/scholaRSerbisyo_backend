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
        'event_image_uuid',
        'event_name',
        'description',
        'date',
        'time_from',
        'time_to',
        'location',
        'status',
        'admin',
        'event_type_id',
        'event_type',
        'submissions',
        'scholar_id',
        'submission_image_uuid',
        'time_in',
        'time_out'
    ];

    protected $casts = [
        'submissions' => 'array',
    ];

    public function eventType() {
        return $this->morphTo();
    }

    public function submissions() {
        return $this->belongsToMany('App\Models\Scholar', 'submissions', 'event_id', 'scholar_id');
    }
}
