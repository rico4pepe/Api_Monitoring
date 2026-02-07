<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    /**
     * Test user can be assigned roles.
     */
    public function test_user_can_be_assigned_role(): void
    {
        $admin = Role::create(['name' => 'admin', 'description' => 'Admin role']);
        $user = User::factory()->create();

        $user->assignRole('admin');

        $this->assertTrue($user->hasRole('admin'));
    }

    /**
     * Test hasAnyRole with multiple roles.
     */
    public function test_has_any_role_with_multiple_roles(): void
    {
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'monitor']);
        Role::create(['name' => 'viewer']);

        $user = User::factory()->create();
        $user->assignRole('monitor');

        $this->assertTrue($user->hasAnyRole(['admin', 'monitor']));
        $this->assertFalse($user->hasAnyRole(['admin', 'viewer']));
    }

    /**
     * Test hasAllRoles requires all specified roles.
     */
    public function test_has_all_roles_requires_all(): void
    {
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'auditor']);

        $user = User::factory()->create();
        $user->assignRole('admin');
        $user->assignRole('auditor');

        $this->assertTrue($user->hasAllRoles(['admin', 'auditor']));
        $this->assertFalse($user->hasAllRoles(['admin', 'viewer']));
    }

    /**
     * Test user can be removed from role.
     */
    public function test_user_can_be_removed_from_role(): void
    {
        Role::create(['name' => 'admin']);
        $user = User::factory()->create();
        
        $user->assignRole('admin');
        $this->assertTrue($user->hasRole('admin'));

        $user->removeRole('admin');
        $this->assertFalse($user->hasRole('admin'));
    }

    /**
     * Test is_active field affects login ability.
     */
    public function test_inactive_user_cannot_login(): void
    {
        $inactive = User::factory()->create([
            'email' => 'inactive@example.com',
            'is_active' => false,
        ]);

        $response = $this->post('/login', [
            'email' => 'inactive@example.com',
            'password' => 'password',
        ]);

        // Should redirect to login (authentication failed)
        $this->assertFalse(auth()->check());
    }

    /**
     * Test user login is recorded.
     */
    public function test_login_records_ip_and_timestamp(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'is_active' => true,
        ]);

        $this->actingAs($user);
        $this->get('/');

        $user->refresh();
        
        $this->assertNotNull($user->last_login_at);
        $this->assertNotNull($user->last_login_ip);
    }
}
