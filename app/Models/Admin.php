<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\User;

class Admin extends User
{
    use HasApiTokens, Notifiable;

    protected $table = 'admins';
    protected $primaryKey = 'admin_id';

    public function adminType() {
        return $this->hasOne('App\Models\AdminType', 'admin_type_id');
    }

    public function user() {
        return $this->belongsTo('App\Models\User', 'user_id');
    }
}
