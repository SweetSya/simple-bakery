<?php

namespace Database\Seeders\Database;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create roles
        $roles = [
            ['name' => 'administrator', 'description' => 'Administrator role with full access'],
            ['name' => 'staff', 'description' => 'Staff role with limited access'],
            ['name' => 'guest', 'description' => 'Guest role with minimal access'],
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
    }
}
