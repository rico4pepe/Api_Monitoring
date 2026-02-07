<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use App\Models\AuditLog;
use Tests\TestCase;

class AuditLoggingTest extends TestCase
{
    /**
     * Test audit log is created when user is created.
     */
    public function test_audit_log_created_on_user_creation(): void
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'audit@example.com',
        ]);

        $audit = AuditLog::where('model_type', 'User')
            ->where('model_id', $user->id)
            ->where('action', 'create')
            ->first();

        $this->assertNotNull($audit);
        $this->assertNull($audit->old_values);
        $this->assertIsArray($audit->new_values);
    }

    /**
     * Test audit log is created when user is updated.
     */
    public function test_audit_log_created_on_user_update(): void
    {
        $user = User::factory()->create(['name' => 'Original Name']);
        
        AuditLog::truncate(); // Clear creation log

        $user->update(['name' => 'Updated Name']);

        $audit = AuditLog::where('model_type', 'User')
            ->where('model_id', $user->id)
            ->where('action', 'update')
            ->first();

        $this->assertNotNull($audit);
        $this->assertEquals('Original Name', $audit->old_values['name']);
        $this->assertEquals('Updated Name', $audit->new_values['name']);
    }

    /**
     * Test audit log contains IP address and user agent.
     */
    public function test_audit_log_contains_request_info(): void
    {
        $user = User::factory()->create();

        $audit = AuditLog::where('model_type', 'User')
            ->where('model_id', $user->id)
            ->first();

        $this->assertNotNull($audit->ip_address);
        $this->assertNotNull($audit->user_agent);
    }

    /**
     * Test audit log scope filters.
     */
    public function test_audit_log_scopes(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $byModel = AuditLog::forModel('User', $user1->id)->get();
        $this->assertTrue($byModel->every(fn($log) => $log->model_id === $user1->id));

        $byAction = AuditLog::byAction('create')->get();
        $this->assertTrue($byAction->every(fn($log) => $log->action === 'create'));
    }
}
