<?php

use App\Services\LanguageService;

if (!function_exists('trans_json')) {
    /**
     * Get translation from JSON files
     */
    function trans_json(string $key, array $replace = [], string $locale = null): string
    {
        $languageService = app(LanguageService::class);
        $translation = $languageService->getTranslation($key, $locale);

        if (!empty($replace)) {
            foreach ($replace as $placeholder => $value) {
                $translation = str_replace(':' . $placeholder, $value, $translation);
            }
        }

        return $translation;
    }
}

if (!function_exists('get_languages')) {
    /**
     * Get all active languages
     */
    function get_languages(): \Illuminate\Database\Eloquent\Collection
    {
        return app(LanguageService::class)->getActiveLanguages();
    }
}

if (!function_exists('get_language_name')) {
    /**
     * Get language name by code
     */
    function get_language_name(string $code): string
    {
        return app(LanguageService::class)->getLanguageName($code);
    }
}

if (!function_exists('get_flag_url')) {
    /**
     * Get flag URL for language code
     */
    function get_flag_url(string $code): string
    {
        return app(LanguageService::class)->getFlagUrl($code);
    }
}

if (!function_exists('is_locale_supported')) {
    /**
     * Check if locale is supported
     */
    function is_locale_supported(string $locale): bool
    {
        return app(LanguageService::class)->isLocaleSupported($locale);
    }
}
