<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    /**
     * Kreira ili ažurira admin korisnika.
     */
    public function run(): void
    {
        // Možeš promeniti email/lozinku po želji ili iz .env
        $email = env('ADMIN_EMAIL', 'admin@example.com');
        $pass  = env('ADMIN_PASSWORD', 'password');

        User::updateOrCreate(
            ['email' => $email],
            [
                'name'              => 'Admin',
                'password'          => Hash::make($pass),
                'role'              => 'admin',   
                'points'            => 0,
                'remember_token'    => Str::random(10),
                'email_verified_at' => now(),
            ]
        );
    }
}

