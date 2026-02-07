<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use App\Models\AuditLog;
//use App\Models\User;

class RecordSuccessfulLogin
{
    public function handle(Login $event): void
    {
        $user = $event->user;
        $request = request();

        // Update last login time + IP
        $user->recordLogin($request->ip());

        // Write audit trail
        AuditLog::create([
            'user_id'    => $user->id,
            'action'     => 'LOGIN',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}

