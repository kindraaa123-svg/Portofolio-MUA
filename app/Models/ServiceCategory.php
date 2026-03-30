<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceCategory extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'slug', 'sort_order'];

    public function services(): HasMany
    {
        return $this->hasMany(Service::class)->where('is_active', true);
    }
}
