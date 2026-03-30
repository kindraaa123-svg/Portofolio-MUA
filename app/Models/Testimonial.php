<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Testimonial extends Model
{
    use SoftDeletes;

    protected $fillable = ['customer_id', 'name', 'title', 'rating', 'message', 'is_published'];

    protected function casts(): array
    {
        return ['is_published' => 'boolean'];
    }
}
