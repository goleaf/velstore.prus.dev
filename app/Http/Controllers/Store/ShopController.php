<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\View\View;

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
        $filters = [
            'category' => array_filter((array) $request->input('category', [])),
            'brand' => array_filter((array) $request->input('brand', [])),
            'price_min' => (int) $request->input('price_min', 0),
            'price_max' => (int) $request->input('price_max', 1000),
            'color' => array_filter((array) $request->input('color', [])),
            'size' => array_filter((array) $request->input('size', [])),
            'shop' => array_filter((array) $request->input('shop', [])),
        ];

        $products = Product::with(['translation', 'variants.attributeValues'])
            ->when(! empty($filters['category']), function ($query) use ($filters) {
                $query->whereIn('category_id', $filters['category']);
            })
            ->when(! empty($filters['brand']), function ($query) use ($filters) {
                $query->whereIn('brand_id', $filters['brand']);
            })
            ->when(! empty($filters['shop']), function ($query) use ($filters) {
                $query->whereIn('shop_id', $filters['shop']);
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
        $shops = Shop::where('status', 'active')->withCount('products')->orderBy('name')->get();

        if ($request->ajax()) {
            return view('themes.xylo.partials.product-list', compact('products'))->render();
        }

        return view('themes.xylo.shop', [
            'products' => $products,
            'categories' => $categories,
            'brands' => $brands,
            'shops' => $shops,
            'filters' => $filters,
            'currentCategory' => $currentCategory,
        ]);
    }
}
