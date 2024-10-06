<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;

class AdminType extends Model
{
    use HasApiTokens, Notifiable;

    protected $table = 'admin_types';

    protected $fillable = [
        'admin_type_name'
    ];

    public function admin() {
        return $this->hasMany('App\Models\Admin');
    }
}
