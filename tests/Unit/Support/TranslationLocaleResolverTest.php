<?php

namespace Tests\Unit\Support;

use App\Support\Admin\TranslationLocaleResolver;
use Illuminate\Support\MessageBag;
use PHPUnit\Framework\TestCase;

class TranslationLocaleResolverTest extends TestCase
{
    public function test_it_prioritizes_old_input_when_available(): void
    {
        $errors = new MessageBag();

        $resolution = TranslationLocaleResolver::resolve(
            ['en', 'de', 'es'],
            $errors,
            ['active_tab' => 'de'],
            'es',
            'en'
        );

        $this->assertSame('de', $resolution->initial());
        $this->assertSame('de', $resolution->old());
        $this->assertNull($resolution->error());
    }

    public function test_it_uses_error_locale_when_present(): void
    {
        $errors = new MessageBag([
            'translations.de.name' => ['Required'],
        ]);

        $resolution = TranslationLocaleResolver::resolve(
            ['en', 'de', 'es'],
            $errors,
            [],
            'es',
            'en'
        );

        $this->assertSame('de', $resolution->initial());
        $this->assertSame('de', $resolution->error());
        $this->assertNull($resolution->old());
    }

    public function test_it_falls_back_to_application_locale(): void
    {
        $errors = new MessageBag();

        $resolution = TranslationLocaleResolver::resolve(
            ['en', 'de', 'es'],
            $errors,
            [],
            'es',
            'en'
        );

        $this->assertSame('es', $resolution->initial());
    }

    public function test_it_falls_back_to_configured_fallback_locale(): void
    {
        $errors = new MessageBag();

        $resolution = TranslationLocaleResolver::resolve(
            ['en', 'de'],
            $errors,
            [],
            'fr',
            'de'
        );

        $this->assertSame('de', $resolution->initial());
    }

    public function test_it_uses_first_available_locale_when_no_preferences_match(): void
    {
        $errors = new MessageBag();

        $resolution = TranslationLocaleResolver::resolve(
            ['it', 'nl'],
            $errors,
            [],
            'fr',
            'de'
        );

        $this->assertSame('it', $resolution->initial());
    }

    public function test_it_defaults_to_english_when_no_locales_exist(): void
    {
        $errors = new MessageBag();

        $resolution = TranslationLocaleResolver::resolve(
            [],
            $errors,
            [],
            null,
            null
        );

        $this->assertSame('en', $resolution->initial());
    }
}

