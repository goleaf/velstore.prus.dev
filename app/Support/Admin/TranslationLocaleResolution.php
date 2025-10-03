<?php

namespace App\Support\Admin;

final class TranslationLocaleResolution
{
    public function __construct(
        private readonly string $initial,
        private readonly ?string $error,
        private readonly ?string $old,
    ) {
    }

    public function initial(): string
    {
        return $this->initial;
    }

    public function error(): ?string
    {
        return $this->error;
    }

    public function old(): ?string
    {
        return $this->old;
    }
}

