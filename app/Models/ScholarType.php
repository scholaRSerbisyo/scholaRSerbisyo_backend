<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;

class ScholarType extends Model
{
    use HasApiTokens, Notifiable;

    protected $table = 'scholar_types';

    protected $primaryKey = 'scholar_type_id';

    protected $fillable = [
        'scholar_type_name',
        'scholar_type_description'
    ];

    public function scholar() {
        return $this->hasMany('App\Models\Scholar', 'scholar_id');
    }
}
