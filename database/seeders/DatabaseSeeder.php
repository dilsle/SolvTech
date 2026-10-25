<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;


class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::create([
            'name' => env('USER_NAME', 'Admin'),
            'email' => env('USER_EMAIL', 'admin@gmail.com'),
            'password' => Hash::make(env('USER_PASSWORD', 'admin123')),
        ]);
    }
}
