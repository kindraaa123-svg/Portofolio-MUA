<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PortfolioImage extends Model
{
    use SoftDeletes;

    protected $fillable = ['portfolio_id', 'image_path', 'alt_text', 'sort_order'];

    public function portfolio(): BelongsTo
    {
        return $this->belongsTo(Portfolio::class);
    }
}
