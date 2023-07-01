<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for($i = 1; $i < 100; $i++) {
            User::create([
                'name' => fake()->userName(),
                'email' => fake()->safeEmail(),
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ]);
        }
    }
}
