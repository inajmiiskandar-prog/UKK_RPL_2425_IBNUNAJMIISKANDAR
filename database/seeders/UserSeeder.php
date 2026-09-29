<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'kode_user' => 'USR-001',
                'nama' => 'Administrator',
                'password' => Hash::make('123456'),
                'jkl' => 'LAKI_LAKI',
                'role' => 'ADMIN',
                'status' => true,
            ]
        );
    }
}
