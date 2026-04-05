<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WebsiteSetting extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'site_name',
        'tagline',
        'logo',
        'favicon',
        'home_banner',
        'contact_phone',
        'contact_email',
        'whatsapp_number',
        'bank_account_number',
        'bank_account_bank_name',
        'address',
        'google_maps_embed',
        'instagram_url',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'theme_primary',
        'theme_secondary',
    ];
}
