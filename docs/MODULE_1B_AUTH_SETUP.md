# Module 1B: Authentication, Roles & Login - Implementation Guide

## Setup

### 1. Run Migrations
```bash
php artisan migrate
```

This creates:
- `roles` table
- `user_roles` table (pivot)
- `audit_logs` table
- Adds security fields to `users` table

### 2. Seed Default Roles
```bash
php artisan db:seed --class=RoleSeeder
# Or with user accounts:
php artisan db:seed
```

This creates 4 default roles:
- **admin** - Full system access, user management, configuration
- **monitor** - Can view and manage API monitors, configure alerts
- **viewer** - Read-only access to dashboard and reports
- **auditor** - View audit logs and system activities

Default test users (for development):
- admin@example.com / admin
- monitor@example.com / monitor
- viewer@example.com / viewer

## Using Roles

### Assigning Roles to Users
```php
// Using model instance
$user->assignRole('admin');
$user->assignRole($adminRole); // Can pass Role instance

// Multiple roles
$user->assignRole('admin');
$user->assignRole('auditor');

// Or in seeder
$user->roles()->attach(Role::where('name', 'admin')->first());
```

### Checking Roles
```php
// In controllers or models
$user->hasRole('admin'); // true if user has admin role

// Multiple roles (OR logic)
$user->hasAnyRole(['admin', 'monitor']); // true if user has at least one

// Multiple roles (AND logic)
$user->hasAllRoles(['admin', 'monitor']); // true only if user has ALL
```

### Using Middleware for Route Protection

Register middleware aliases in `bootstrap/app.php`:
```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'role' => \App\Http\Middleware\CheckRole::class,
        'role.all' => \App\Http\Middleware\CheckAllRoles::class,
    ]);
})
```

In routes:
```php
// Require at least one role
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware('auth', 'role:admin,monitor');

// Require all roles
Route::post('/critical-action', [ActionController::class, 'critical'])
    ->middleware('auth', 'role.all:admin,auditor');

// Route group
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('users', UserController::class);
    Route::resource('roles', RoleController::class);
});
```

## Audit Logging

All changes to `User` and `Role` models are automatically logged via the `AuditableObserver`.

### Viewing Audit Logs
```php
use App\Models\AuditLog;

// Get all audit entries
AuditLog::all();

// By model
AuditLog::forModel('User', 1)->get(); // All changes to User #1

// By user
AuditLog::byUser(1)->get(); // All actions by user #1

// By action
AuditLog::byAction('update')->get(); // All updates

// With relationships
AuditLog::with('user')->get();
```

### Audit Log Structure
Each log entry contains:
- `user_id` - Who made the change (null if system)
- `action` - 'create', 'update', 'delete'
- `model_type` - Class name (e.g., 'User', 'Role')
- `model_id` - ID of changed record
- `old_values` - Previous state (JSON)
- `new_values` - New state (JSON)
- `ip_address` - Request IP
- `user_agent` - Browser/client info
- `created_at` - Timestamp

## Recording User Logins

Register login middleware in `bootstrap/app.php`:
```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->web([
        \App\Http\Middleware\RecordLogin::class,
    ]);
})
```

This automatically updates `last_login_at` and `last_login_ip` on user's first request after authentication.

## Adding Audit Logging to Other Models

To add audit logging to additional models:

1. Use observer pattern:
```php
// In model
use App\Observers\AuditableObserver;

class ApiMonitor extends Model
{
    // ...
}

// In AuditServiceProvider.php boot()
ApiMonitor::observe(AuditableObserver::class);
```

2. Or implement a custom observer following the same pattern in `app/Observers/`

## Security Notes
- All audit logs are immutable (not deleted, only view-filtered)
- Audit entries with `null` user_id indicate system-initiated changes
- IP addresses are stored for every change for compliance tracking
- Failed audit logging is logged but doesn't break requests
