<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Incident extends Model
{
    //
       protected $fillable = [
        'service_type',
        'scope_type',
        'scope_code',
        'status',
        'failure_rate',
        'started_at',
        'resolved_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];
}
