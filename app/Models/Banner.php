<?php

namespace App\Models;

use App\Models\BannerTranslation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;

class Banner extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'status',
        'type',
        'display_location',
        'priority',
        'starts_at',
        'ends_at',
    ];

    protected $casts = [
        'status' => 'boolean',
        'priority' => 'integer',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    /**
     * Get the translations for the banner.
     */
    public function translations()
    {
        return $this->hasMany(BannerTranslation::class);
    }

    public function translation()
    {
        return $this->hasOne(BannerTranslation::class)->where('language_code', App::getLocale());
    }

    public function scopeActive($query)
    {
        $now = now();

        return $query->where('status', 1)
            ->where(function ($query) use ($now) {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where(function ($query) use ($now) {
                $query->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
            });
    }

    public function scopeForLocation($query, string $location, bool $includeGlobal = true)
    {
        return $query->where(function ($query) use ($location, $includeGlobal) {
            $query->where('display_location', $location);

            if ($includeGlobal) {
                $query->orWhere('display_location', 'global');
            }
        });
    }

    public function scopeOrdered($query)
    {
        return $query->orderByDesc('priority')->orderByDesc('starts_at')->orderByDesc('id');
    }

    public function resolveTranslation(?string $locale = null, ?string $fallback = null): ?BannerTranslation
    {
        $locale = $locale ?: App::getLocale();
        $fallback = $fallback ?: config('app.fallback_locale');

        $translations = $this->relationLoaded('translations') ? $this->translations : $this->translations()->get();

        return $translations->firstWhere('language_code', $locale)
            ?: ($fallback ? $translations->firstWhere('language_code', $fallback) : null)
            ?: $translations->first();
    }

    public function imageUrl(?string $locale = null, ?string $fallback = null): ?string
    {
        $translation = $this->resolveTranslation($locale, $fallback);

        return $translation ? $translation->resolvedImageUrl() : null;
    }
}
