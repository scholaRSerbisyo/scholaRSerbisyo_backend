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
        return $this->hasMany(Scholar::class, 'baranggay_id');
    }

    public function events() {
        return $this->hasMany(Event::class, 'baranggay_id');
    }
}