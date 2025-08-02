<?php

namespace Database\Seeders\Database;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Administrator',
                'email' => 'administrator@bakery.com',
                'password' => bcrypt('password'),
                'role_id' => Role::where('name', 'administrator')->first()->id,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Staff',
                'email' => 'staff@bakery.com',
                'password' => bcrypt('password'),
                'role_id' => Role::where('name', 'staff')->first()->id,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Guest',
                'email' => 'guest@bakery.com',
                'password' => bcrypt('password'),
                'role_id' => Role::where('name', 'guest')->first()->id,
                'email_verified_at' => now(),
            ],
        ];

        foreach ($users as $user) {
            User::create($user);
        }
    }
}
