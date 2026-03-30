<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BookingDetail extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'booking_id', 'service_id', 'addon_id', 'type', 'item_name', 'price', 'qty', 'line_total',
    ];

    protected function casts(): array
    {
        return ['price' => 'decimal:2', 'line_total' => 'decimal:2'];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
