<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'status',
        'template',
        'show_in_navigation',
        'show_in_footer',
        'is_featured',
        'published_at',
    ];

    protected $casts = [
        'status' => 'boolean',
        'show_in_navigation' => 'boolean',
        'show_in_footer' => 'boolean',
        'is_featured' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function translations()
    {
        return $this->hasMany(PageTranslation::class);
    }

    public function translation($languageCode = null)
    {
        $languageCode = $languageCode ?? app()->getLocale();

        return $this->translations()->where('language_code', $languageCode)->first();
    }

    public function scopePublished($query)
    {
        return $query
            ->where('status', true)
            ->where(function ($query) {
                $query
                    ->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
    }
}
