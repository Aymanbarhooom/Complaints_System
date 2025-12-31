<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@gov.com'],
            [
                'firstName' => 'System',
                'lastName'  => 'Admin',
                'cardId'    => null,
                'birthday'  => null,
                'password'  => Hash::make('admin123'),
                'role'      => 'admin',
                'agency_id' => null,
            ]
        );
    }
}
