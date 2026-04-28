<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Data User 1: Manual/Reguler
        User::create([
            'name' => 'Rafif Muh',
            'email' => 'rafif@monefy.com',
            'password' => Hash::make('password123'),
            'google_id' => null,
            'avatar' => null,
        ]);

        // Data User 2: Simulasi User dari Google Login
        User::create([
            'name' => 'Qanita Shafiyah',
            'email' => 'qanita@gmail.com',
            'password' => null, // Dikosongkan karena login via Google
            'google_id' => '1234567890abcdef',
            'avatar' => 'https://lh3.googleusercontent.com/a/ac-avatar-qanita',
        ]);
    }
}