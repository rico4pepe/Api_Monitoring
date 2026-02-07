<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataSource extends Model
{
    //
        protected $fillable = [
        'code',
        'type',
        'config',
        'cursor',
        'is_active',
        'last_polled_at',
    ];

    protected $casts = [
        'config' => 'array',
        'is_active' => 'boolean',
        ];
}
