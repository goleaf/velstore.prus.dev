<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    use HasFactory;

    // The table associated with the model (optional if it's singular version of the model name)
    protected $table = 'site_settings';

    // The attributes that are mass assignable
    protected $fillable = [
        'site_name',
        'tagline',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'contact_email',
        'support_email',
        'contact_phone',
        'support_hours',
        'address',
        'logo',
        'favicon',
        'primary_color',
        'secondary_color',
        'facebook_url',
        'twitter_url',
        'instagram_url',
        'linkedin_url',
        'maintenance_mode',
        'maintenance_message',
        'footer_text',
    ];

    // The attributes that should be cast to native types
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'maintenance_mode' => 'boolean',
    ];

    // Optional: You may want to include any relationships (e.g., translations, if necessary)
}
