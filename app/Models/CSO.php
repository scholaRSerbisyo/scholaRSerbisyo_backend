<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CSO extends Model
{
    use HasFactory;

    protected $table = 'csos';
    protected $primaryKey = 'cso_id';

    protected $fillable = [
        'cso_name',
        'description'
    ];

    public function scholars() {
        return $this->hasMany(Scholar::class, 'cso_id');
    }

    public function events() {
        return $this->hasMany(Event::class, 'cso_id');
    }
}