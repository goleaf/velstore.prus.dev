<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class ProductVariantController extends Controller
{
    public function index()
    {
        $languages = Language::active()->orderBy('name')->get();

        return view('admin.product_variants.index', compact('languages'));
    }

    public function getData(Request $request)
    {
        $locale = app()->getLocale();

        $productVariants = ProductVariant::query()
            ->with([
                'product.translations' => function ($query) use ($locale) {
                    $query->where('language_code', $locale);
                },
                'translations' => function ($query) use ($locale) {
                    $query->where('language_code', $locale);
                },
            ])
            ->select('product_variants.*');

        return DataTables::of($productVariants)
            ->addColumn('id', fn (ProductVariant $productVariant) => $productVariant->id)
            ->addColumn('product', function (ProductVariant $productVariant) {
                $productTranslation = $productVariant->product?->translations->first();

                return $productTranslation?->name ?? __('cms.product_variants.not_available');
            })
            ->addColumn('variant_name', function (ProductVariant $productVariant) {
                if (filled($productVariant->name)) {
                    return $productVariant->name;
                }

                $translation = $productVariant->translations->first();

                return $translation?->name ?? __('cms.product_variants.not_available');
            })
            ->addColumn('value', function (ProductVariant $productVariant) {
                if (filled($productVariant->value)) {
                    return $productVariant->value;
                }

                return optional($productVariant->translations->first())->value
                    ?? __('cms.product_variants.not_available');
            })
            ->addColumn('SKU', fn (ProductVariant $productVariant) => $productVariant->SKU)
            ->addColumn('action', function (ProductVariant $productVariant) {
                $editRoute = route('admin.product_variants.edit', $productVariant->id);
                $editLabel = e(__('cms.product_variants.edit_button'));
                $deleteLabel = e(__('cms.product_variants.delete_button'));

                return <<<HTML
                    <div class="flex flex-col gap-2">
                        <button type="button"
                                class="btn btn-outline btn-sm w-full btn-edit-variant"
                                data-url="{$editRoute}" title="{$editLabel}">
                            {$editLabel}
                        </button>
                        <button type="button"
                                class="btn btn-outline-danger btn-sm w-full btn-delete-variant"
                                data-id="{$productVariant->id}" title="{$deleteLabel}">
                            {$deleteLabel}
                        </button>
                    </div>
                HTML;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function create()
    {
        $products = Product::where('status', 1)
            ->with(['translations' => function ($query) {
                $query->where('language_code', app()->getLocale());
            }])
            ->get();

        $languages = Language::active()->orderBy('name')->get();

        return view('admin.product_variants.create', compact('products', 'languages'));
    }

    public function store(Request $request)
    {
        $this->normalizeRequest($request);

        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'variant_slug' => ['nullable', 'string', 'max:255', 'unique:product_variants,variant_slug'],
            'name' => ['required', 'string', 'max:255'],
            'value' => ['nullable', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'discount_price' => ['nullable', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'SKU' => ['required', 'string', 'max:255', 'unique:product_variants,SKU'],
            'barcode' => ['nullable', 'string', 'max:255'],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'dimensions' => ['nullable', 'string', 'max:255'],
            'translations' => ['required', 'array'],
            'translations.*.name' => ['required', 'string', 'max:255'],
            'translations.*.value' => ['nullable', 'string', 'max:255'],
        ]);

        $translations = $validated['translations'];
        unset($validated['translations']);

        $variantData = $this->prepareVariantPayload($validated);

        DB::transaction(function () use ($translations, $variantData) {
            $variant = ProductVariant::create($variantData);

            foreach ($translations as $languageCode => $translation) {
                $variant->translations()->create([
                    'language_code' => $languageCode,
                    'name' => $translation['name'],
                    'value' => $translation['value'] ?? null,
                ]);
            }
        });

        return redirect()
            ->route('admin.product_variants.index')
            ->with('success', __('cms.product_variants.create_success'));
    }

    public function edit($id)
    {
        $productVariant = ProductVariant::with('translations')->findOrFail($id);

        $products = Product::where('status', 1)
            ->with(['translations' => function ($query) {
                $query->where('language_code', app()->getLocale());
            }])
            ->get();

        $languages = Language::active()->orderBy('name')->get();

        return view('admin.product_variants.edit', compact('productVariant', 'products', 'languages'));
    }

    public function update(Request $request, $id)
    {
        $this->normalizeRequest($request);

        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'variant_slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('product_variants', 'variant_slug')->ignore($id),
            ],
            'name' => ['required', 'string', 'max:255'],
            'value' => ['nullable', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'discount_price' => ['nullable', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'SKU' => [
                'required',
                'string',
                'max:255',
                Rule::unique('product_variants', 'SKU')->ignore($id),
            ],
            'barcode' => ['nullable', 'string', 'max:255'],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'dimensions' => ['nullable', 'string', 'max:255'],
            'translations' => ['required', 'array'],
            'translations.*.name' => ['required', 'string', 'max:255'],
            'translations.*.value' => ['nullable', 'string', 'max:255'],
        ]);

        $translations = $validated['translations'];
        unset($validated['translations']);

        $productVariant = ProductVariant::findOrFail($id);
        $variantData = $this->prepareVariantPayload($validated, $productVariant->id);

        DB::transaction(function () use ($productVariant, $translations, $variantData) {
            $productVariant->update($variantData);

            $languageCodes = array_keys($translations);

            foreach ($translations as $languageCode => $translation) {
                $productVariant->translations()->updateOrCreate(
                    ['language_code' => $languageCode],
                    [
                        'name' => $translation['name'],
                        'value' => $translation['value'] ?? null,
                    ]
                );
            }

            $productVariant->translations()
                ->whereNotIn('language_code', $languageCodes)
                ->delete();
        });

        return redirect()
            ->route('admin.product_variants.index')
            ->with('success', __('cms.product_variants.update_success'));
    }

    public function destroy($id)
    {
        try {
            $productVariant = ProductVariant::findOrFail($id);
            $productVariant->translations()->delete();
            $productVariant->delete();

            return response()->json([
                'success' => true,
                'message' => __('cms.product_variants.delete_success_message'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('cms.product_variants.delete_error_message'),
            ]);
        }
    }

    protected function prepareVariantPayload(array $data, ?int $ignoreId = null): array
    {
        $name = trim($data['name']);
        $slugSource = isset($data['variant_slug']) ? trim($data['variant_slug']) : $name;
        $data['variant_slug'] = $this->generateUniqueSlug($slugSource, $ignoreId);
        $data['name'] = $name;
        $value = $data['value'] ?? null;
        $data['value'] = filled($value) ? trim((string) $value) : null;
        $data['price'] = (float) $data['price'];
        $data['discount_price'] = isset($data['discount_price']) ? (float) $data['discount_price'] : null;
        $data['stock'] = (int) $data['stock'];
        $data['weight'] = isset($data['weight']) ? (float) $data['weight'] : null;

        if (isset($data['SKU'])) {
            $data['SKU'] = trim((string) $data['SKU']);
        }

        foreach (['barcode', 'dimensions'] as $stringField) {
            if (array_key_exists($stringField, $data)) {
                $value = trim((string) $data[$stringField]);
                $data[$stringField] = $value !== '' ? $value : null;
            }
        }

        return $data;
    }

    protected function normalizeRequest(Request $request): void
    {
        $nullableFields = ['variant_slug', 'value', 'discount_price', 'barcode', 'weight', 'dimensions'];

        foreach ($nullableFields as $field) {
            if (! $request->filled($field)) {
                $request->merge([$field => null]);
            }
        }
    }

    protected function generateUniqueSlug(string $source, ?int $ignoreId = null): string
    {
        $base = Str::slug($source);

        if ($base === '') {
            $base = 'variant';
        }

        $slug = $base;
        $counter = 1;

        while (ProductVariant::where('variant_slug', $slug)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $base . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
