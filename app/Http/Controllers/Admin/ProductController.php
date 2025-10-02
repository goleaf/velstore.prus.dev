<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductStatusUpdateRequest;
use App\Http\Requests\Admin\ProductStoreRequest;
use App\Http\Requests\Admin\ProductUpdateRequest;
use App\Models\Attribute;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Language;
use App\Models\Product;
use App\Models\ProductAttributeValue;
use App\Models\Vendor;
use App\Services\Admin\CategoryService;
use App\Services\Admin\ProductService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    protected $categoryService;

    protected $productService;

    public function __construct(CategoryService $categoryService, ProductService $productService)
    {
        $this->categoryService = $categoryService;
        $this->productService = $productService;
    }

    public function index(Request $request)
    {
        $query = Product::with([
            'translation',
            'translations',
            'category.translation',
            'brand.translation',
            'primaryVariant',
            'thumbnail',
        ])->select('products.*');

        $search = $request->input('search');
        if (! empty($search)) {
            $query->where(function ($builder) use ($search) {
                $builder->where('SKU', 'like', "%{$search}%")
                    ->orWhereHas('translations', function ($translationQuery) use ($search) {
                        $translationQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $status = $request->input('status');
        if ($status === 'active') {
            $query->whereIn('status', [1, '1', true, 'active']);
        } elseif ($status === 'inactive') {
            $query->whereIn('status', [0, '0', false, 'inactive']);
        }

        $sort = $request->input('sort', 'latest');
        if ($sort === 'oldest') {
            $query->oldest();
        } else {
            $query->latest();
        }

        $perPage = (int) $request->input('per_page', 15);
        $perPage = in_array($perPage, [10, 15, 25, 50], true) ? $perPage : 15;

        $products = $query->paginate($perPage)->withQueryString();

        $stats = [
            'total' => Product::count(),
            'active' => Product::whereIn('status', [1, '1', true, 'active'])->count(),
            'inactive' => Product::whereIn('status', [0, '0', false, 'inactive'])->count(),
        ];

        return view('admin.products.index', [
            'products' => $products,
            'filters' => [
                'search' => $search,
                'status' => $status,
                'sort' => $sort,
                'per_page' => $perPage,
            ],
            'stats' => $stats,
        ]);
    }

    public function create()
    {
        $lookups = $this->formLookups();

        $product = new Product();
        $product->setRelation('translations', collect());
        $product->setRelation('variants', collect());
        $product->setRelation('images', collect());

        return view('admin.products.create', array_merge($lookups, [
            'product' => $product,
            'isEdit' => false,
            'productMetrics' => null,
        ]));
    }

    public function store(ProductStoreRequest $request)
    {
        $defaultLang = config('app.locale');

        DB::transaction(function () use ($request, $defaultLang) {
            $defaultName = $request->input("translations.$defaultLang.name", 'product');
            $slug = $this->generateUniqueSlug($defaultName);

            $product = Product::create([
                'shop_id' => 1,
                'vendor_id' => $request->vendor_id,
                'slug' => $slug,
                'category_id' => $request->category_id,
                'brand_id' => $request->brand_id,
                'product_type' => 'variable',
            ]);

            foreach ($request->translations as $lang => $data) {
                $product->translations()->create([
                    'language_code' => $lang,
                    'name' => $data['name'],
                    'description' => $data['description'] ?? null,
                    'short_description' => $data['short_description'] ?? null,
                    'tags' => $data['tags'] ?? null,
                ]);
            }

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = $image->store('products', 'public');
                    $product->images()->create([
                        'name' => $image->getClientOriginalName(),
                        'image_url' => $path,
                        'type' => 'thumb',
                    ]);
                }
            }

            $this->syncVariants($product, $request->input('variants', []), $defaultLang);
        });

        return redirect()->route('admin.products.index')->with('success', __('cms.products.success_create'));
    }

    public function generateUniqueSlug($name)
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $count = 1;
        while (Product::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count;
            $count++;
        }

        return $slug;
    }

    public function edit($id)
    {
        $product = Product::with([
            'translations',
            'variants.translations',
            'variants.attributeValues',
            'images',
            'orderItems',
            'reviews',
            'category.translation',
            'brand.translation',
            'vendor',
        ])->findOrFail($id);

        $lookups = $this->formLookups();
        $sizes = $lookups['sizes'];
        $colors = $lookups['colors'];

        $sizeAttributeId = $sizes->first()?->attribute_id;
        $colorAttributeId = $colors->first()?->attribute_id;

        foreach ($product->variants as $variant) {
            $variant->size_id = $sizeAttributeId
                ? optional($variant->attributeValues->firstWhere('attribute_id', $sizeAttributeId))->id
                : null;
            $variant->color_id = $colorAttributeId
                ? optional($variant->attributeValues->firstWhere('attribute_id', $colorAttributeId))->id
                : null;
        }

        $productMetrics = $this->buildProductMetrics($product);

        return view('admin.products.edit', array_merge($lookups, [
            'product' => $product,
            'isEdit' => true,
            'productMetrics' => $productMetrics,
        ]));
    }

    public function update(ProductUpdateRequest $request, $id)
    {
        $product = Product::findOrFail($id);
        $defaultLang = config('app.locale');
        DB::transaction(function () use ($request, $product, $defaultLang) {
            $product->update([
                'category_id' => $request->category_id,
                'brand_id' => $request->brand_id,
                'vendor_id' => $request->vendor_id,
            ]);

            foreach ($request->translations as $lang => $data) {
                $product->translations()->updateOrCreate(
                    ['language_code' => $lang],
                    [
                        'name' => $data['name'],
                        'description' => $data['description'] ?? null,
                        'short_description' => $data['short_description'] ?? null,
                        'tags' => $data['tags'] ?? null,
                    ]
                );
            }

            if ($request->filled('remove_images')) {
                foreach ((array) $request->remove_images as $imageId) {
                    $image = $product->images()->find($imageId);
                    if ($image) {
                        Storage::disk('public')->delete($image->image_url);
                        $image->delete();
                    }
                }
            }

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = $image->store('products', 'public');
                    $product->images()->create([
                        'name' => $image->getClientOriginalName(),
                        'image_url' => $path,
                        'type' => 'thumb',
                    ]);
                }
            }

            $this->syncVariants($product, $request->input('variants', []), $defaultLang);
        });

        return redirect()->route('admin.products.index')->with('success', __('cms.products.success_update'));
    }

    public function destroy($id)
    {
        try {
            if ($this->productService->destroy($id)) {
                return redirect()->back()->with('success', __('cms.products.success_delete'));
            }

            return redirect()->back()->with('error', __('cms.products.delete_failed'));
        } catch (\Exception $e) {
            \Log::error("Error deleting product with ID {$id}: " . $e->getMessage());

            return redirect()->back()->with('error', __('cms.products.delete_error'));
        }
    }

    public function updateStatus(ProductStatusUpdateRequest $request)
    {
        $product = Product::findOrFail($request->input('id'));
        $product->status = $request->boolean('status');
        $product->save();

        return redirect()->back()->with('success', __('cms.products.status_updated'));
    }

    protected function formLookups(): array
    {
        $languages = Language::orderBy('name')->get();
        $categories = Category::with('translation')->get();
        $brands = Brand::with('translation')->get();
        $vendors = Vendor::all();

        $sizeAttribute = Attribute::with('values')->where('name', 'Size')->first();
        $colorAttribute = Attribute::with('values')->where('name', 'Color')->first();

        $sizes = $sizeAttribute?->values ?? collect();
        $colors = $colorAttribute?->values ?? collect();

        return compact('languages', 'categories', 'brands', 'vendors', 'sizes', 'colors');
    }

    protected function syncVariants(Product $product, array $variants, string $defaultLang): void
    {
        $normalized = collect($variants)
            ->map(fn (array $variant) => [
                'name' => $variant['name'] ?? null,
                'price' => $variant['price'] ?? null,
                'discount_price' => $variant['discount_price'] ?? null,
                'stock' => $variant['stock'] ?? null,
                'SKU' => $variant['SKU'] ?? null,
                'barcode' => $variant['barcode'] ?? null,
                'weight' => $variant['weight'] ?? null,
                'dimensions' => $variant['dimensions'] ?? null,
                'size_id' => $variant['size_id'] ?? null,
                'color_id' => $variant['color_id'] ?? null,
            ])
            ->filter(fn ($variant) => filled($variant['name']))
            ->values();

        DB::table('product_variant_attribute_values')
            ->where('product_id', $product->id)
            ->delete();

        $product->variants()->delete();

        $attachedAttributeIds = [];

        foreach ($normalized as $index => $variantData) {
            foreach ($variantData as $key => $value) {
                if ($value === '') {
                    $variantData[$key] = null;
                }
            }

            $price = $variantData['price'];
            if ($price === null || $variantData['name'] === null) {
                continue;
            }

            $variantData['price'] = (float) $price;
            $variantData['stock'] = $variantData['stock'] !== null ? (int) $variantData['stock'] : 0;
            $variantData['discount_price'] = $variantData['discount_price'] !== null ? (float) $variantData['discount_price'] : null;
            $variantData['weight'] = $variantData['weight'] !== null ? (float) $variantData['weight'] : null;

            $slugBase = Str::slug($variantData['name']) ?: Str::slug('variant');

            $variant = $product->variants()->create([
                'variant_slug' => $slugBase . '-' . uniqid(),
                'price' => $variantData['price'],
                'discount_price' => $variantData['discount_price'],
                'stock' => $variantData['stock'],
                'SKU' => $variantData['SKU'],
                'barcode' => $variantData['barcode'],
                'weight' => $variantData['weight'],
                'dimensions' => $variantData['dimensions'],
                'is_primary' => $index === 0,
            ]);

            $variant->translations()->create([
                'language_code' => $defaultLang,
                'name' => $variantData['name'],
            ]);

            foreach (['size_id', 'color_id'] as $key) {
                $attributeValueId = $variantData[$key] ?? null;
                if ($attributeValueId) {
                    DB::table('product_variant_attribute_values')->insert([
                        'product_id' => $product->id,
                        'product_variant_id' => $variant->id,
                        'attribute_value_id' => $attributeValueId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    ProductAttributeValue::firstOrCreate([
                        'product_id' => $product->id,
                        'attribute_value_id' => $attributeValueId,
                    ]);

                    if (! in_array($attributeValueId, $attachedAttributeIds, true)) {
                        $attachedAttributeIds[] = $attributeValueId;
                    }
                }
            }
        }

        $query = ProductAttributeValue::where('product_id', $product->id);
        if (! empty($attachedAttributeIds)) {
            $query->whereNotIn('attribute_value_id', $attachedAttributeIds);
        }
        $query->delete();
    }

    protected function buildProductMetrics(Product $product): array
    {
        $variants = $product->variants ?? collect();
        $orderItems = $product->orderItems ?? collect();
        $reviews = $product->reviews ?? collect();

        $lowStockThreshold = 5;

        $totalStock = $variants->sum(fn ($variant) => (int) ($variant->stock ?? 0));
        $lowStockCount = $variants->filter(fn ($variant) => (int) ($variant->stock ?? 0) <= $lowStockThreshold)->count();
        $totalSales = $orderItems->sum(fn ($item) => (int) ($item->quantity ?? 0));
        $totalRevenue = $orderItems->reduce(function ($carry, $item) {
            $price = (float) ($item->price ?? 0);
            $quantity = (int) ($item->quantity ?? 0);

            return $carry + ($price * $quantity);
        }, 0.0);
        $reviewCount = $reviews->count();
        $averageRating = $reviewCount > 0 ? round((float) $reviews->avg('rating'), 2) : null;
        $lastSoldAt = optional($orderItems->sortByDesc('created_at')->first())->created_at;

        return [
            'total_variants' => $variants->count(),
            'total_stock' => $totalStock,
            'low_stock_count' => $lowStockCount,
            'low_stock_threshold' => $lowStockThreshold,
            'total_sales' => $totalSales,
            'total_revenue' => $totalRevenue,
            'average_rating' => $averageRating,
            'review_count' => $reviewCount,
            'last_sold_at' => $lastSoldAt,
        ];
    }
}
