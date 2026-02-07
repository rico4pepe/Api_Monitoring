<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'admin',
                'description' => 'Full system access, user management, configuration',
            ],
            [
                'name' => 'monitor',
                'description' => 'Can view and manage API monitors, configure alerts',
            ],
            [
                'name' => 'viewer',
                'description' => 'Read-only access to dashboard and reports',
            ],
            [
                'name' => 'auditor',
                'description' => 'View audit logs and system activities',
            ],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(
                ['name' => $role['name']],
                ['description' => $role['description']]
            );
        }
    }
}
