<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class EventValidation extends Model
{
    use HasFactory;

    protected $table = 'event_validations';
    protected $primaryKey = 'event_validation_id';
    
    protected $fillable = [
        'admin_id',
        'admin_type_name',
        'event_image_uuid',
        'event_name',
        'description',
        'date',
        'time_from',
        'time_to',
        'location',
        'status',
        'event_type_id',
        'school_id',
        'baranggay_id',
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
        'time_from' => 'datetime:H:i',
        'time_to' => 'datetime:H:i',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = ['formatted_date', 'formatted_time_from', 'formatted_time_to'];

    public function getFormattedDateAttribute()
    {
        return $this->date ? $this->date->format('Y-m-d') : null;
    }

    public function getFormattedTimeFromAttribute()
    {
        return $this->time_from ? Carbon::parse($this->time_from)->format('H:i') : null;
    }

    public function getFormattedTimeToAttribute()
    {
        return $this->time_to ? Carbon::parse($this->time_to)->format('H:i') : null;
    }

    public function eventType()
    {
        return $this->belongsTo(EventType::class, 'event_type_id', 'event_type_id');
    }

    public function school()
    {
        return $this->belongsTo(School::class, 'school_id', 'school_id');
    }

    public function barangay()
    {
        return $this->belongsTo(Baranggay::class, 'baranggay_id', 'baranggay_id');
    }

    public function admin() {
        return $this->belongsTo(Admin::class, 'admin_id', 'admin_id');
    }

    public function adminType() {
        return $this->belongsTo(AdminType::class, 'admin_type_name', 'name');
    }

    public function isCSOEvent()
    {
        return $this->eventType && $this->eventType->name === 'CSO';
    }
}