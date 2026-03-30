<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BlockedSchedule extends Model
{
    use SoftDeletes;

    protected $fillable = ['blocked_date', 'start_time', 'end_time', 'reason', 'is_full_day'];

    protected function casts(): array
    {
        return ['blocked_date' => 'date', 'is_full_day' => 'boolean'];
    }
}
