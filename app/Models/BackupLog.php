<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BackupLog extends Model
{
    use SoftDeletes;

    protected $fillable = ['file_name', 'file_path', 'type', 'file_size', 'status', 'notes', 'created_by'];
}
