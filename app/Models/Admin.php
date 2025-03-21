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

    protected $fillable = [
        'admin_name',
        'admin_type_id',
        'user_id',
    ];

    public function adminType()
    {
        return $this->belongsTo('App\Models\AdminType', 'admin_type_id');
    }

    public function user() 
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }
}
