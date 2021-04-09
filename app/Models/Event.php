<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'valid_from',
        'valid_to',
        'gps_lat',
        'gps_lng',
        'user_id',
    ];

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}
