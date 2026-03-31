<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecycleBin extends Model
{
    protected $table = 'recycle_bin';

    protected $fillable = ['module', 'model_type', 'model_id', 'payload', 'deleted_by', 'deleted_at'];

    protected function casts(): array
    {
        return ['payload' => 'array', 'deleted_at' => 'datetime'];
    }

    public function deletedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}
