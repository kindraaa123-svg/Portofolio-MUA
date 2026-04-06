<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MonthlyExpense extends Model
{
    protected $fillable = [
        'period_month',
        'amount',
        'note',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'period_month' => 'date',
        'amount' => 'float',
    ];
}
