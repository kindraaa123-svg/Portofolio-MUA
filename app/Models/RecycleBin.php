<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecycleBin extends Model
{
    protected $table = 'recycle_bin';

    protected $fillable = ['module', 'model_type', 'model_id', 'payload', 'deleted_by', 'deleted_at'];

    protected function casts(): array
    {
        return ['payload' => 'array', 'deleted_at' => 'datetime'];
    }
}
