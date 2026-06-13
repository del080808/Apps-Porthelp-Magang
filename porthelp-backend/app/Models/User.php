<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory; 
use App\Models\Ticket;       
use App\Models\Notification;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'phone', 'department'
    ];

    protected $hidden = ['password'];

    // Relasi: pelapor punya banyak tiket
    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'user_id');
    }

    // Relasi: teknisi handle banyak tiket
    public function assignedTickets()
    {
        return $this->hasMany(Ticket::class, 'teknisi_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    // Helper cek role
    public function isAdmin() { return $this->role === 'admin'; }
    public function isTeknisi() { return $this->role === 'teknisi'; }
    public function isPelapor() { return $this->role === 'pelapor'; }
}