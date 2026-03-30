<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AvailabilitySlot extends Model
{
    use SoftDeletes;

    protected $fillable = ['day_of_week', 'start_time', 'end_time', 'max_bookings', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
