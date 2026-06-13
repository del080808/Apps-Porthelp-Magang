<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'user_id', 'title', 'message', 
        'type', 'is_read', 'ticket_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}