<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PageController extends Controller
{
    public function show(string $slug)
    {
        $locale = app()->getLocale();
        $fallbackLocale = config('app.locale');

        $page = Page::published()
            ->where('slug', $slug)
            ->with(['translations' => function ($query) use ($locale, $fallbackLocale) {
                $query->whereIn('language_code', [$locale, $fallbackLocale]);
            }])
            ->firstOrFail();

        $translation = $page->translations->firstWhere('language_code', $locale)
            ?? $page->translations->firstWhere('language_code', $fallbackLocale)
            ?? $page->translations->first();

        abort_if(is_null($translation), 404);

        $metaTitle = $translation->meta_title ?: $translation->title;
        $metaDescription = $translation->meta_description
            ?: Str::limit(strip_tags($translation->excerpt ?: $translation->content), 160);

        $relatedPages = Page::published()
            ->where('id', '<>', $page->id)
            ->with(['translations' => function ($query) use ($locale, $fallbackLocale) {
                $query->whereIn('language_code', [$locale, $fallbackLocale]);
            }])
            ->orderBy('published_at', 'desc')
            ->take(3)
            ->get()
            ->map(function (Page $related) use ($locale, $fallbackLocale) {
                $relatedTranslation = $related->translations->firstWhere('language_code', $locale)
                    ?? $related->translations->firstWhere('language_code', $fallbackLocale)
                    ?? $related->translations->first();

                if (!$relatedTranslation) {
                    return null;
                }

                return [
                    'title' => $relatedTranslation->title,
                    'excerpt' => $relatedTranslation->excerpt,
                    'slug' => $related->slug,
                    'image_url' => $relatedTranslation->image_url
                        ? Storage::url($relatedTranslation->image_url)
                        : null,
                ];
            })
            ->filter()
            ->values();

        return view('themes.xylo.pages.show', [
            'page' => $page,
            'translation' => $translation,
            'metaTitle' => $metaTitle,
            'metaDescription' => $metaDescription,
            'relatedPages' => $relatedPages,
        ]);
    }
}
