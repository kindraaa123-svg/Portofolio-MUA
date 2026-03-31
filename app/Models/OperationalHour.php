<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OperationalHour extends Model
{
    protected $fillable = [
        'day_of_week',
        'day_name',
        'open_time',
        'close_time',
        'is_closed',
    ];

    protected function casts(): array
    {
        return [
            'is_closed' => 'boolean',
        ];
    }
}
