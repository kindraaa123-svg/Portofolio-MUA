<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MediaFile extends Model
{
    use SoftDeletes;

    protected $fillable = ['module', 'file_name', 'file_path', 'mime_type', 'file_size', 'uploaded_by'];
}
