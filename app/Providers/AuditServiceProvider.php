<?php

namespace App\Providers;

use App\Models\User;
use App\Models\Role;
use App\Observers\AuditableObserver;
use Illuminate\Support\ServiceProvider;

class AuditServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register observers to track model changes
        User::observe(AuditableObserver::class);
        Role::observe(AuditableObserver::class);
    }
}
