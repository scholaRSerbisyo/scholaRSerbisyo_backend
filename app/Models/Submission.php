<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;

class Submission extends Model
{
    use HasApiTokens, Notifiable;

    protected $table = 'submissions';
    protected $primaryKey = 'submission_id';

    protected $fillable = [
        'image_uuid',
        'time_in',
        'time_out',
        'scholar_id'
    ];

    public function scholar()
    {
        return $this->belongsTo('App\Models\Scholar', 'scholar_id');
    }

    public function event() {
        return $this->belongsTo('App\Models\Event', 'event_id');
    }
}
