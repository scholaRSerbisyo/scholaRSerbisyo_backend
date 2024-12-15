<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'comment_text',
        'event_id',
        'scholar_id',
    ];

    public function scholar()
    {
        return $this->belongsTo(Scholar::class, 'scholar_id');
    }

    public function replies()
    {
        return $this->hasMany(Reply::class, 'comment_id');
    }

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id');
    }
}

