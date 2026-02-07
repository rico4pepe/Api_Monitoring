<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed roles first
        $this->call(RoleSeeder::class);

        // Create a test admin user
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'is_active' => true,
        ]);
        $admin->assignRole('admin');

        // Create a test monitor user
        $monitor = User::factory()->create([
            'name' => 'Monitor User',
            'email' => 'monitor@example.com',
            'is_active' => true,
        ]);
        $monitor->assignRole('monitor');

        // Create a test viewer user
        $viewer = User::factory()->create([
            'name' => 'Viewer User',
            'email' => 'viewer@example.com',
            'is_active' => true,
        ]);
        $viewer->assignRole('viewer');
    }
}
