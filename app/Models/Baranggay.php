<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Baranggay extends Model
{
    use HasFactory;

    protected $table = 'baranggays';

    protected $fillable = [
        'baranggay_name',
        'address'
    ];

    public function scholars() {
        return $this->hasMany('App\Models\Scholar');
    }
}
