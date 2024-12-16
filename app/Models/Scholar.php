<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Scholar extends User
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'scholars';

    protected $primaryKey = 'scholar_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'profile_image_uuid',
        'firstname',
        'lastname',
        'birthdate',
        'gender',
        'course',
        'age',
        'mobilenumber',
        'yearlevel',
        'scholar_type_id',
        'user_id',
        'school_id',
        'baranggay_id',
        'push_token'
    ];

    protected $casts = [
        'age' => 'integer',
        'birthdate' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->scholar_id = self::generateUniqueId();
        });
    }

    private static function generateUniqueId()
    {
        do {
            $id = str_pad(mt_rand(1000000, 9999999), 7, '0', STR_PAD_LEFT);
        } while (self::where('scholar_id', $id)->exists());

        return $id;
    }

    public function scholarType()
    {
        return $this->belongsTo(ScholarType::class, 'scholar_type_id', 'scholar_type_id');
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

    public function events()
    {
        return $this->belongsToMany(Event::class, 'submissions', 'scholar_id', 'event_id')
                    ->withPivot('time_in_image_uuid', 'time_out_image_uuid', 'time_in', 'time_out')
                    ->withTimestamps();
    }

    public function getFullNameAttribute()
    {
        return "{$this->firstname} {$this->lastname}";
    }

    public function returnServices()
    {
        return $this->hasMany(ReturnService::class, 'return_service_id');
    }

    public function getCurrentYearReturnServiceStatus()
    {
        $currentYear = date('Y');
        $returnServices = $this->returnServices()
            ->where('year', $currentYear)
            ->get();

        $acceptedSubmissions = $returnServices->count();
        $status = $acceptedSubmissions >= 5 ? 'complete' : 'incomplete';

        return [
            'acceptedSubmissions' => $acceptedSubmissions,
            'status' => $status
        ];
    }
}

