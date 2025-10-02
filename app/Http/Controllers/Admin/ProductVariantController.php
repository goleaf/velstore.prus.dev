<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class ProductVariantController extends Controller
{
    public function index()
    {
        /* $productVariants = ProductVariant::with('product.translations', 'translations')
         ->paginate(10);

         $languages = Language::active()->get();

         return view('admin.product_variants.index', compact('productVariants', 'languages')); */

        $languages = Language::active()->get(); // Get active languages

        return view('admin.product_variants.index', compact('languages'));
    }

    public function getData(Request $request)
    {
        $productVariants = ProductVariant::with('product', 'translations')
            ->select('product_variants.*'); // Use select to avoid eager loading too much data

        return DataTables::of($productVariants)
            ->addColumn('id', function ($productVariant) {
                return $productVariant->id;  // Add the ID here
            })
            ->addColumn('product', function ($productVariant) {
                return $productVariant->product->translations->first()->name ?? 'Unknown Product';
            })
            ->addColumn('variant_name', function ($productVariant) {
                return $productVariant->translations->first()->name ?? 'N/A';
            })
            ->addColumn('action', function ($productVariant) {
                $editRoute = route('admin.product_variants.edit', $productVariant->id);
                $editLabel = e(__('cms.products.edit_button'));
                $deleteLabel = e(__('cms.coupons.delete_button'));

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

        $languages = Language::active()->get();

        return view('admin.product_variants.create', compact('products', 'languages'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
            'translations' => 'required|array',
            'translations.*.name' => 'required|string|max:255',
            'translations.*.value' => 'nullable|string|max:255',
        ]);

        $translations = $request->input('translations');
        $productVariantData = $request->except('translations');

        $productVariantData['variant_slug'] = Str::slug($request->input('name'));

        $productVariant = ProductVariant::create($productVariantData);

        foreach ($translations as $locale => $translation) {
            $productVariant->translations()->create([
                'locale' => $locale,
                'name' => $translation['name'],
                'value' => $translation['value'] ?? null,
            ]);
        }

        return redirect()->route('admin.product_variants.index')->with('success', 'Product Variant created successfully.');
    }

    public function edit($id)
    {
        $productVariant = ProductVariant::with('translations')->findOrFail($id);

        $products = Product::where('status', 1)
            ->with(['translations' => function ($query) {
                $query->where('language_code', app()->getLocale());
            }])
            ->get();

        $languages = Language::active()->get();

        return view('admin.product_variants.edit', compact('productVariant', 'products', 'languages'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'variant_slug' => 'required|unique:product_variants,variant_slug,'.$id,
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
            'translations' => 'required|array',
            'translations.*.name' => 'required|string|max:255',
            'translations.*.value' => 'nullable|string|max:255',
        ]);

        $translations = $request->input('translations');
        $productVariantData = $request->except('translations');

        $productVariantData['variant_slug'] = Str::slug($request->input('name'));

        $productVariant = ProductVariant::findOrFail($id);
        $productVariant->update($productVariantData);

        foreach ($translations as $locale => $translation) {
            $productVariant->translations()->updateOrCreate(
                ['locale' => $locale],
                [
                    'name' => $translation['name'],
                    'value' => $translation['value'] ?? null,
                ]
            );
        }

        return redirect()->route('admin.product_variants.index')->with('success', 'Product Variant updated successfully.');
    }

    public function destroy($id)
    {
        try {
            $productVariant = ProductVariant::findOrFail($id);
            $productVariant->translations()->delete();
            $productVariant->delete();

            return response()->json([
                'success' => true,
                'message' => 'Product Variant deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the product variant.',
            ]);
        }
    }
}
