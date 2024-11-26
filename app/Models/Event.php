<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
        'date' => 'date',
        'time_from' => 'datetime',
        'time_to' => 'datetime',
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
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

    public function attendances()
    {
        // return $this->hasMany(EventAttendance::class, 'event_id');
    }

    public function attendees()
    {
        return $this->belongsToMany(Scholar::class, 'event_attendances', 'event_id', 'scholar_id')
                    ->withPivot('time_in_image_uuid', 'time_out_image_uuid', 'time_in', 'time_out')
                    ->withTimestamps();
    }

    public function isCSOEvent()
    {
        return $this->eventType->name === 'CSO';
    }
}

