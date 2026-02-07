<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
        'last_login_at',    // ADD THIS
        'last_login_ip',    // ADD THIS
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get all roles assigned to this user.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }

    /**
     * Get all audit logs created by this user.
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    /**
     * Check if user has a specific role.
     */
   public function hasRole(string $roleName): bool
    {
        return $this->relationLoaded('roles')
            ? $this->roles->contains('name', $roleName)
            : $this->roles()->where('name', $roleName)->exists();
    }

    /**
     * Check if user has any of the given roles.
     */
    public function hasAnyRole(array|string $roleNames): bool
    {
        $roles = is_array($roleNames) ? $roleNames : [$roleNames];

        return $this->relationLoaded('roles')
        ? $this->roles->whereIn('name', $roles)->isNotEmpty()
        : $this->roles()->whereIn('name', $roles)->exists();
    }

    /**
     * Check if user has all of the given roles.
     */
   public function hasAllRoles(array $roleNames): bool
        {
            return $this->roles()
                ->whereIn('name', $roleNames)
                ->count() === count(array_unique($roleNames));
        }


    /**
     * Assign a role to the user.
     */
    public function assignRole(string|Role $role): void
    {
        $roleId = $role instanceof Role ? $role->id : Role::whereName($role)->firstOrFail()->id;
        if (!$this->roles()->where('role_id', $roleId)->exists()) {
            $this->roles()->attach($roleId);
        }
    }

    /**
     * Remove a role from the user.
     */
    public function removeRole(string|Role $role): void
    {
        $roleId = $role instanceof Role ? $role->id : Role::whereName($role)->firstOrFail()->id;
        $this->roles()->detach($roleId);
    }

    /**
     * Update last login timestamp and IP.
     *
     * @param string $ipAddress
     * @return void
     */
    public function recordLogin(string $ipAddress): void
    {
        $this->update([
            'last_login_at' => now(),
            'last_login_ip' => $ipAddress,
        ]);
    }

     /**
     * isActive
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }
    
   
}
