<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'service_category_id', 'name', 'slug', 'description', 'duration_minutes', 'price',
        'is_home_service_available', 'home_service_fee', 'is_active', 'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'home_service_fee' => 'decimal:2',
            'is_home_service_available' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }
}
