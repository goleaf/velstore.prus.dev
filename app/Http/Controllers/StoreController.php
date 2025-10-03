<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Category;
use App\Models\Product;

class StoreController extends Controller
{
    public function index()
    {
        $locale = app()->getLocale();

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
            ->withCount(['reviews as reviews_count' => fn ($query) => $query->approved()])
            ->withAvg(['reviews as reviews_avg_rating' => fn ($query) => $query->approved()], 'rating')
            ->orderBy('id', 'desc')
            ->take(10)
            ->get()
            ->map(function ($product) {
                $product->reviews_avg_rating = round((float) ($product->reviews_avg_rating ?? 0), 1);

                return $product;
            });

        return view('themes.xylo.home', compact('banners', 'categories', 'products'));
    }
}
