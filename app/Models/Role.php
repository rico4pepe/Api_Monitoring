<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
//use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends \Illuminate\Database\Eloquent\Model
{
    use HasFactory;

    protected $fillable = ['name', 'description'];

    protected $casts = [
    'name' => 'string',
];

    /**
     * Get all users that have this role.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_roles');
    }

    /**
     * Check if a role has a specific permission (for future use).
     */
    public function hasPermission(string $permission): bool
    {
        // This can be extended with a permissions system later
        return true;
    }
}
