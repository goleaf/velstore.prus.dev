<?php

namespace App\View\Composers;

use App\Models\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class StorePageComposer
{
    public function compose(View $view): void
    {
        if (!Schema::hasTable('pages') || !Schema::hasTable('page_translations')) {
            return;
        }

        $locale = app()->getLocale();
        $fallbackLocale = config('app.locale');

        $view->with([
            'navigationPages' => $this->resolvePages(
                Page::published()
                    ->where('show_in_navigation', true)
                    ->orderByDesc('published_at')
                    ->limit(6)
                    ->with(['translations' => function ($query) use ($locale, $fallbackLocale) {
                        $query->whereIn('language_code', [$locale, $fallbackLocale]);
                    }])
                    ->get(),
                $locale,
                $fallbackLocale
            ),
            'footerPages' => $this->resolvePages(
                Page::published()
                    ->where('show_in_footer', true)
                    ->orderByDesc('published_at')
                    ->limit(8)
                    ->with(['translations' => function ($query) use ($locale, $fallbackLocale) {
                        $query->whereIn('language_code', [$locale, $fallbackLocale]);
                    }])
                    ->get(),
                $locale,
                $fallbackLocale
            ),
        ]);
    }

    protected function resolvePages(Collection $pages, string $locale, string $fallbackLocale): Collection
    {
        return $pages
            ->map(function (Page $page) use ($locale, $fallbackLocale) {
                $translation = $page->translations->firstWhere('language_code', $locale)
                    ?? $page->translations->firstWhere('language_code', $fallbackLocale)
                    ?? $page->translations->first();

                if (!$translation) {
                    return null;
                }

                return (object) [
                    'title' => $translation->title,
                    'slug' => $page->slug,
                    'excerpt' => $translation->excerpt,
                    'url' => route('store.pages.show', $page->slug),
                ];
            })
            ->filter()
            ->values();
    }
}
