<?php

namespace App\Support\Admin;

use Illuminate\Support\Collection;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;

final class TranslationLocaleResolver
{
    /**
     * @param iterable<int, string|null> $languageCodes
     */
    public static function resolve(
        iterable $languageCodes,
        MessageBag|ViewErrorBag $errors,
        array $oldInput,
        ?string $appLocale,
        ?string $fallbackLocale,
        string $default = 'en'
    ): TranslationLocaleResolution {
        $codes = self::normalizeCodes($languageCodes);

        if ($errors instanceof ViewErrorBag) {
            $errors = $errors->getBag('default');
        }

        $errorTab = $codes->first(
            static fn (string $code): bool => $errors->has("translations.$code.name") || $errors->has("translations.$code.value")
        );

        $oldTab = $oldInput['active_tab'] ?? null;
        if (! is_string($oldTab) || ! $codes->contains($oldTab)) {
            $oldTab = null;
        }

        $appLocale = is_string($appLocale) && $codes->contains($appLocale) ? $appLocale : null;
        $fallbackLocale = is_string($fallbackLocale) && $codes->contains($fallbackLocale) ? $fallbackLocale : null;

        $initial = $oldTab
            ?? $errorTab
            ?? $appLocale
            ?? $fallbackLocale
            ?? $codes->first()
            ?? $default;

        return new TranslationLocaleResolution($initial, $errorTab, $oldTab);
    }

    /**
     * @param iterable<int, string|null> $languageCodes
     * @return Collection<int, string>
     */
    private static function normalizeCodes(iterable $languageCodes): Collection
    {
        return collect($languageCodes)
            ->filter(static fn ($code): bool => is_string($code) && $code !== '')
            ->values()
            ->unique()
            ->values();
    }
}
