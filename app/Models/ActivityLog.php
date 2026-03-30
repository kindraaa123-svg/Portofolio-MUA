<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'user_name',
        'user_level',
        'module',
        'action',
        'subject_type',
        'subject_id',
        'properties',
        'ip_address',
        'geo_location',
        'latitude',
        'longitude',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'properties' => 'array',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
