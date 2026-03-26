<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')->insert([
            [
                'name' => 'Cedric',
                'email' => 'cedric@example.com',
                'password' => Hash::make('12345678'),
                'coins' => 100,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Selena',
                'email' => 'selena@example.com',
                'password' => Hash::make('12345678'),
                'coins' => 250,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Aileen',
                'email' => 'aileen@example.com',
                'password' => Hash::make('12345678'),
                'coins' => 500,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Mikael',
                'email' => 'mikael@example.com',
                'password' => Hash::make('12345678'),
                'coins' => 500,
                'email_verified_at' => now(),
            ],
        ]);
    }
}