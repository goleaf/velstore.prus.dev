<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class BannerTranslation extends Model
{
    use HasFactory;

    // Define the fillable attributes
    protected $fillable = [
        'banner_id',
        'language_code',
        'title',
        'description',
        'button_text',
        'button_url',
        'image_url',
    ];

    protected $casts = [
        'button_text' => 'string',
        'button_url' => 'string',
    ];

    // Relationship with Banner
    public function banner()
    {
        return $this->belongsTo(Banner::class);
    }

    public function resolvedImageUrl(): ?string
    {
        if (! $this->image_url) {
            return null;
        }

        if (str_starts_with($this->image_url, ['http://', 'https://'])) {
            return $this->image_url;
        }

        $normalized = str_starts_with($this->image_url, 'public/')
            ? substr($this->image_url, strlen('public/'))
            : $this->image_url;

        if (Storage::disk('public')->exists($normalized)) {
            return Storage::disk('public')->url($normalized);
        }

        if (Storage::exists($normalized)) {
            return Storage::url($normalized);
        }

        return asset($normalized);
    }

    public function resolvedButtonUrl(): ?string
    {
        if (! $this->button_url) {
            return null;
        }

        if (str_starts_with($this->button_url, ['http://', 'https://'])) {
            return $this->button_url;
        }

        return url($this->button_url);
    }
}
