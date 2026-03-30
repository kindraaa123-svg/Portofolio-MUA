<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'booking_code', 'customer_id', 'booking_date', 'booking_time', 'location_type', 'location_address',
        'notes', 'subtotal', 'addon_total', 'home_service_total', 'grand_total', 'dp_amount', 'payment_status',
        'status', 'handled_by',
    ];

    protected function casts(): array
    {
        return [
            'booking_date' => 'date',
            'subtotal' => 'decimal:2',
            'addon_total' => 'decimal:2',
            'home_service_total' => 'decimal:2',
            'grand_total' => 'decimal:2',
            'dp_amount' => 'decimal:2',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(BookingDetail::class);
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(BookingStatusLog::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(BookingPayment::class);
    }
}
