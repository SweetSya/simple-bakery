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
            ['name' => 'administrator2', 'description' => 'Administrator role with full access'],
            ['name' => 'staff2', 'description' => 'Staff role with limited access'],
            ['name' => 'guest2', 'description' => 'Guest role with minimal access'],
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
    }
}
