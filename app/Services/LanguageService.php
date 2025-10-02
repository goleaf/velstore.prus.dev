<?php

namespace App\Services;

use App\Models\Language;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class LanguageService
{
    /**
     * Get all active languages
     */
    public function getActiveLanguages(): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember('active_languages', 3600, function () {
            return Language::where('active', 1)->orderBy('name')->get();
        });
    }

    /**
     * Get translation for a specific key and language
     */
    public function getTranslation(string $key, string $locale = null): string
    {
        $locale = $locale ?: app()->getLocale();
        $fallbackLocale = config('app.fallback_locale', 'en');

        // Try to get translation from JSON file
        $translation = $this->getFromJson($key, $locale);

        if ($translation === $key && $locale !== $fallbackLocale) {
            // Fallback to default locale
            $translation = $this->getFromJson($key, $fallbackLocale);
        }

        return $translation;
    }

    /**
     * Get translation from JSON file
     */
    private function getFromJson(string $key, string $locale): string
    {
        $filePath = resource_path("lang/{$locale}.json");

        if (!File::exists($filePath)) {
            return $key;
        }

        $translations = json_decode(File::get($filePath), true);

        if (!is_array($translations)) {
            return $key;
        }

        return data_get($translations, $key, $key);
    }

    /**
     * Get all translations for a specific locale
     */
    public function getAllTranslations(string $locale): array
    {
        $filePath = resource_path("lang/{$locale}.json");

        if (!File::exists($filePath)) {
            return [];
        }

        $translations = json_decode(File::get($filePath), true);

        return is_array($translations) ? $translations : [];
    }

    /**
     * Set translation for a specific key and locale
     */
    public function setTranslation(string $key, string $value, string $locale): bool
    {
        $filePath = resource_path("lang/{$locale}.json");

        $translations = [];
        if (File::exists($filePath)) {
            $translations = json_decode(File::get($filePath), true) ?: [];
        }

        data_set($translations, $key, $value);

        return File::put($filePath, json_encode($translations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) !== false;
    }

    /**
     * Get language by code
     */
    public function getLanguageByCode(string $code): ?Language
    {
        return Cache::remember("language_{$code}", 3600, function () use ($code) {
            return Language::where('code', $code)->where('active', 1)->first();
        });
    }

    /**
     * Clear language cache
     */
    public function clearCache(): void
    {
        Cache::forget('active_languages');

        // Clear individual language caches
        foreach ($this->getActiveLanguages() as $language) {
            Cache::forget("language_{$language->code}");
        }
    }

    /**
     * Get available locales
     */
    public function getAvailableLocales(): array
    {
        return $this->getActiveLanguages()->pluck('code')->toArray();
    }

    /**
     * Check if locale is supported
     */
    public function isLocaleSupported(string $locale): bool
    {
        return in_array($locale, $this->getAvailableLocales());
    }

    /**
     * Get language name by code
     */
    public function getLanguageName(string $code): string
    {
        $language = $this->getLanguageByCode($code);
        return $language ? $language->name : $code;
    }

    /**
     * Get flag URL for language code
     */
    public function getFlagUrl(string $code): string
    {
        $flagMap = [
            'en' => 'us',
            'ar' => 'sa',
            'fa' => 'ir',
            'hi' => 'in',
            'id' => 'id',
            'ja' => 'jp',
            'ko' => 'kr',
            'nl' => 'nl',
            'pl' => 'pl',
            'pt' => 'pt',
            'ru' => 'ru',
            'th' => 'th',
            'tr' => 'tr',
            'vi' => 'vn',
            'zh' => 'cn',
        ];

        $countryCode = $flagMap[$code] ?? $code;
        return "https://flagcdn.com/w40/{$countryCode}.png";
    }
}
