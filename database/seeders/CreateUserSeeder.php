<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CreateUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'karven@os.my'],
            [
                'name' => 'Karven',
                'email' => 'karven@os.my',
                'password' => Hash::make('h3lloworld'),
                'email_verified_at' => now(),
            ]
        );
    }
}