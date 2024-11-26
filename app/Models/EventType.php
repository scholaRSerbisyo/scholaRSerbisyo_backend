<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventType extends Model
{
    protected $table = 'event_types';
    protected $primaryKey = 'event_type_id';

    protected $fillable = [
        'name',
        'description'
    ];

    public function events() {
        return $this->hasMany(Event::class);
    }
}