<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@le-hub.local'],
            [
                'name'     => 'Lucas',
                'password' => Hash::make('change-me-123'),
                'role'     => 'admin',
            ]
        );
    }
}
