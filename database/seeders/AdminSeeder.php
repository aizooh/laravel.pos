<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['username' => 'isaack'],
            [
                'name'     => 'Isaack',
                'email'    => 'isaack@mamicar.local',
                'password' => Hash::make('admin123'),
                'role'     => 'admin',
                'active'   => true,
            ]
        );
    }
}