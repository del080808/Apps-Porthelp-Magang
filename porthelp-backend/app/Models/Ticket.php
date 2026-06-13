<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $fillable = [
        'title', 'description', 'status', 
        'priority', 'category', 'user_id', 
        'teknisi_id', 'resolution_notes', 'resolved_at'
    ];

    public function pelapor()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function teknisi()
    {
        return $this->belongsTo(User::class, 'teknisi_id');
    }
}