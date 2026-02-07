<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    //

    protected $fillable = [
        'raw_request',
        'raw_response',
        'occurred_at',
        'status',
        'source',
        'client_code',
        'service_type',
        'vendor_code',
        'endpoint',
        'reference',
        'phone',
        'error_code',
        'latency_ms',

    ];  

    protected $casts = [
        'raw_request' => 'array',
        'raw_response' => 'array',
        'occurred_at' => 'datetime',
        ];
}
