<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;

class EventType extends Model
{
    use HasApiTokens, Notifiable;

    protected $table = 'event_types';

    protected $fillable = [
        'event_type_name',
        'event_type_description'
    ];

    public function admin() {
        return $this->hasOne('App\Models\Admin');
    }

    public function events() {
        return $this->hasMany('App\Models\Event');
    }
}
