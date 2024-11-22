<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Baranggay extends Model
{
    use HasFactory;

    protected $table = 'baranggays';

    protected $primaryKey = 'baranggay_id';

    protected $fillable = [
        'baranggay_name',
        'address'
    ];

    public function scholars() {
        return $this->hasMany('App\Models\Scholar', 'scholar_id');
    }

    public function events() {
        return $this->morphMany(Event::class, 'event_type');
    }
}
