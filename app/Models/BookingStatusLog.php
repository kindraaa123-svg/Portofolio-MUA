<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingStatusLog extends Model
{
    protected $fillable = ['booking_id', 'old_status', 'new_status', 'note', 'created_by'];
}
