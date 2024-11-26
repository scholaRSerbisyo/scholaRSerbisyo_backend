<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Scholar extends User
{
    use HasApiTokens, Notifiable, HasFactory;

    protected $table = 'scholars';

    protected $primaryKey = 'scholar_id';

    protected $fillable = [
        'firstname',
        'lastname',
        'age',
        'address',
        'mobilenumber',
        'yearlevel',
        'scholar_type_id',
        'user_id',
        'school_id',
        'baranggay_id'
    ];

    protected $casts = [
        'age' => 'integer',
    ];

    public function scholarType()
    {
        return $this->belongsTo(ScholarType::class, 'scholar_type_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function school()
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    public function baranggay()
    {
        return $this->belongsTo(Baranggay::class, 'baranggay_id');
    }

    public function submissions()
    {
        return $this->hasMany(Submission::class, 'scholar_id');
    }

    public function attendedEvents()
    {
        return $this->belongsToMany(Event::class, 'submissions', 'scholar_id', 'event_id')
                    ->withPivot('time_in_image_uuid', 'time_out_image_uuid', 'time_in', 'time_out')
                    ->withTimestamps();
    }

    public function getFullNameAttribute()
    {
        return "{$this->firstname} {$this->lastname}";
    }
}

