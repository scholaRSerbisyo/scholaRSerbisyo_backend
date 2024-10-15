<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    use HasFactory;

    protected $table = 'schools';

    protected $primaryKey = 'school_id';

    protected $fillable = [
        'school_name',
        'address'
    ];

    public function scholars() {
        return $this->hasMany(Scholar::class, 'scholar_id');
    }
}
