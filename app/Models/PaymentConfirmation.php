<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentConfirmation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'booking_id', 'payer_name', 'bank_name', 'amount', 'transfer_at', 'proof_image', 'status', 'note', 'verified_by',
    ];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'transfer_at' => 'datetime'];
    }
}
