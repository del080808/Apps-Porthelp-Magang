<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'name'     => 'Admin PortHelp',
            'email'    => 'admin@porthelp.com',
            'password' => Hash::make('password123'),
            'role'     => 'admin',
        ]);

        User::create([
            'name'     => 'Budi Teknisi',
            'email'    => 'teknisi@porthelp.com',
            'password' => Hash::make('password123'),
            'role'     => 'teknisi',
        ]);

        User::create([
            'name'       => 'Siti Pelapor',
            'email'      => 'pelapor@porthelp.com',
            'password'   => Hash::make('password123'),
            'role'       => 'pelapor',
            'department' => 'Keuangan',
        ]);
    }
}