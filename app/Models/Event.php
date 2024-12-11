<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Event extends Model
{
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
        'admin_id',
        'event_type_id',
        'school_id',
        'baranggay_id',
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
        'time_from' => 'datetime:H:i',
        'time_to' => 'datetime:H:i',
    ];

    protected $appends = ['formatted_date', 'formatted_time_from', 'formatted_time_to'];

    public function getFormattedDateAttribute()
    {
        return $this->date->format('Y-m-d');
    }

    public function getFormattedTimeFromAttribute()
    {
        return Carbon::parse($this->time_from)->format('H:i');
    }

    public function getFormattedTimeToAttribute()
    {
        return Carbon::parse($this->time_to)->format('H:i');
    }

    public function eventType()
    {
        return $this->belongsTo(EventType::class, 'event_type_id');
    }

    public function school()
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    public function barangay()
    {
        return $this->belongsTo(Baranggay::class, 'baranggay_id');
    }

    public function submissions()
    {
        return $this->hasMany(Submission::class, 'event_id');
    }

    public function scholars()
    {
        return $this->belongsToMany(Scholar::class, 'submissions', 'event_id', 'scholar_id')
                    ->withPivot('time_in_image_uuid', 'time_out_image_uuid', 'time_in', 'time_out')
                    ->withTimestamps();
    }

    public function isCSOEvent()
    {
        return $this->eventType->name === 'CSO';
    }
}

