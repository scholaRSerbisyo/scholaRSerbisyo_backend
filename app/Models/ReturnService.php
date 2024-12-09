<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Scholar;
use App\Models\Submission;

class ReturnService extends Model
{
    use HasFactory;

    protected $table = 'return_services';
    protected $primaryKey = 'return_service_id';

    protected $fillable = ['scholar_id', 'submission_id', 'event_id', 'year', 'completed_at'];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    public function scholar()
    {
        return $this->belongsTo(Scholar::class, 'scholar_id');
    }

    public function submission()
    {
        return $this->belongsTo(Submission::class, 'submission_id');
    }

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id');
    }
}
