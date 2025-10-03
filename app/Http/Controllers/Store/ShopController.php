<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        return $this->renderShopPage($request);
    }

    public function showCategory(Request $request, string $slug)
    {
        $category = Category::with('translation')->where('slug', $slug)->firstOrFail();

        $categoryFilter = $request->input('category', []);
        $categoryIds = is_array($categoryFilter) ? $categoryFilter : [$categoryFilter];
        $categoryIds[] = $category->id;

        $request->merge(['category' => array_unique(array_filter($categoryIds))]);

        return $this->renderShopPage($request, $category);
    }

    protected function renderShopPage(Request $request, ?Category $currentCategory = null): View|string
    {
        $categoryFilter = array_filter((array) $request->input('category', []), static fn ($value) => $value !== null && $value !== '');
        $brandFilter = array_filter((array) $request->input('brand', []), static fn ($value) => $value !== null && $value !== '');
        $colorFilter = array_map(
            static fn ($value) => is_string($value) ? ucfirst(strtolower($value)) : $value,
            array_filter((array) $request->input('color', []), static fn ($value) => $value !== null && $value !== '')
        );
        $sizeFilter = array_filter((array) $request->input('size', []), static fn ($value) => $value !== null && $value !== '');

        $filters = [
            'category' => array_map('intval', $categoryFilter),
            'brand' => array_map('intval', $brandFilter),
            'price_min' => max(0, (int) $request->input('price_min', 0)),
            'price_max' => max(0, (int) $request->input('price_max', 1000)),
            'color' => $colorFilter,
            'size' => $sizeFilter,
        ];

        if ($filters['price_min'] > $filters['price_max']) {
            [$filters['price_min'], $filters['price_max']] = [$filters['price_max'], $filters['price_min']];
        }

        $products = Product::with(['translation', 'variants.attributeValues'])
            ->when(! empty($filters['category']), function ($query) use ($filters) {
                $query->whereIn('category_id', $filters['category']);
            })
            ->when(! empty($filters['brand']), function ($query) use ($filters) {
                $query->whereIn('brand_id', $filters['brand']);
            })
            ->whereHas('variants', function ($variantQuery) use ($filters) {
                $variantQuery
                    ->when($filters['price_min'], function ($q) use ($filters) {
                        $q->where('price', '>=', $filters['price_min']);
                    })
                    ->when($filters['price_max'], function ($q) use ($filters) {
                        $q->where('price', '<=', $filters['price_max']);
                    })
                    ->when(! empty($filters['color']), function ($q) use ($filters) {
                        $q->whereHas('attributeValues', function ($avQuery) use ($filters) {
                            $avQuery->whereIn('value', $filters['color'])
                                ->whereHas('attribute', function ($aQuery) {
                                    $aQuery->where('name', 'Color');
                                });
                        });
                    })
                    ->when(! empty($filters['size']), function ($q) use ($filters) {
                        $q->whereHas('attributeValues', function ($avQuery) use ($filters) {
                            $avQuery->whereIn('value', $filters['size'])
                                ->whereHas('attribute', function ($aQuery) {
                                    $aQuery->where('name', 'Size');
                                });
                        });
                    });
            })
            ->paginate(12)
            ->appends($request->query());

        $brands = Brand::with('translation')->withCount('products')->get();
        $categories = Category::with('translation')->withCount('products')->get();

        if ($request->ajax()) {
            return view('themes.xylo.partials.product-list', compact('products'))->render();
        }

        return view('themes.xylo.shop', [
            'products' => $products,
            'categories' => $categories,
            'brands' => $brands,
            'filters' => $filters,
            'currentCategory' => $currentCategory,
        ]);
    }
}
