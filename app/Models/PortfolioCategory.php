<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PortfolioCategory extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'slug', 'sort_order'];

    public function portfolios(): HasMany
    {
        return $this->hasMany(Portfolio::class)->latest();
    }
}
