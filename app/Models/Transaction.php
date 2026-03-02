<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    //

    protected $fillable = [
         'data_source_id',
        'raw_request',
        'raw_response',
        'occurred_at',
        'status',
        'raw_status',
        'source',
        'client_code',
        'service_type',
        'amount',
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
        'amount' => 'decimal:2',
        ];
}
