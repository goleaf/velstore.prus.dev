<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Category;
use App\Models\Page;
use App\Models\Product;

class StoreController extends Controller
{
    public function index()
    {
        $locale = app()->getLocale();
        $fallbackLocale = config('app.locale');

        $banners = Banner::where('status', 1)
            ->with('translation')
            ->orderBy('id', 'desc')
            ->take(3)
            ->get();

        $categories = Category::where('status', 1)
            ->with('translation')
            ->orderBy('id', 'desc')
            ->take(10)
            ->get();

        $products = Product::where('status', 1)
            ->with(['translation', 'thumbnail', 'primaryVariant'])
            ->withCount('reviews')
            ->orderBy('id', 'desc')
            ->take(10)
            ->get();

        $featuredPages = Page::published()
            ->where('is_featured', true)
            ->with(['translations' => function ($query) use ($locale, $fallbackLocale) {
                $query->whereIn('language_code', [$locale, $fallbackLocale]);
            }])
            ->orderByDesc('published_at')
            ->take(3)
            ->get()
            ->map(function (Page $page) use ($locale, $fallbackLocale) {
                $translation = $page->translations->firstWhere('language_code', $locale)
                    ?? $page->translations->firstWhere('language_code', $fallbackLocale)
                    ?? $page->translations->first();

                if (!$translation) {
                    return null;
                }

                return [
                    'title' => $translation->title,
                    'excerpt' => $translation->excerpt,
                    'slug' => $page->slug,
                    'image_url' => $translation->image_url,
                ];
            })
            ->filter()
            ->values();

        return view('themes.xylo.home', compact('banners', 'categories', 'products', 'featuredPages'));
    }
}
