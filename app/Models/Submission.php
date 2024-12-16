<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Submission extends Model
{
    protected $primaryKey = 'submission_id';

    protected $table = 'submissions';

    protected $fillable = [
        'event_id',
        'scholar_id',
        'time_in_image_uuid',
        'time_in_location',
        'time_out_image_uuid',
        'time_out_location',
        'time_in',
        'time_out',
        'status'
    ];

    protected $casts = [
        'time_in' => 'datetime',
        'time_out' => 'datetime',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id', 'event_id');
    }

    public function scholar()
    {
        return $this->belongsTo(Scholar::class, 'scholar_id', 'scholar_id');
    }
}

