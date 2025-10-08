<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->withoutTwoFactor()->create([
            'name' => 'Admin',
            'email' => 'admin@devafora.com',
            'password' => bcrypt('password'),
        ]);
    }
}
