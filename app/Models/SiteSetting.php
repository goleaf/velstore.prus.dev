<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SiteSetting extends Model
{
    use HasFactory;

    // The table associated with the model (optional if it's singular version of the model name)
    protected $table = 'site_settings';

    // The attributes that are mass assignable
    protected $fillable = [
        'site_name',
        'tagline',
        'top_bar_message',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'logo',
        'favicon',
        'contact_email',
        'contact_phone',
        'address',
        'footer_text',
        'facebook_url',
        'instagram_url',
        'twitter_url',
        'linkedin_url',
    ];

    // The attributes that should be cast to native types
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = [
        'logo_url',
        'favicon_url',
    ];

    public function getLogoUrlAttribute(): ?string
    {
        return $this->resolveMediaUrl($this->logo);
    }

    public function getFaviconUrlAttribute(): ?string
    {
        return $this->resolveMediaUrl($this->favicon);
    }

    protected function resolveMediaUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        if (Str::startsWith($path, ['assets/', 'images/', '/'])) {
            return asset(ltrim($path, '/'));
        }

        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->url($path);
        }

        return asset($path);
    }
}
