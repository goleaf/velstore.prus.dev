<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BrandStatusUpdateRequest;
use App\Http\Requests\Admin\BrandStoreRequest;
use App\Http\Requests\Admin\BrandUpdateRequest;
use App\Models\Brand;
use App\Models\Language;
use App\Services\Admin\BrandService;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    protected $brandService;

    public function __construct(BrandService $brandService)
    {
        $this->brandService = $brandService;
    }

    public function index(Request $request)
    {
        $statusLabels = [
            'active' => __('cms.brands.status_active'),
            'inactive' => __('cms.brands.status_inactive'),
            'discontinued' => __('cms.brands.status_discontinued'),
        ];

        $sortOptions = [
            'latest' => __('cms.brands.sort_latest'),
            'oldest' => __('cms.brands.sort_oldest'),
            'name_asc' => __('cms.brands.sort_name_asc'),
            'name_desc' => __('cms.brands.sort_name_desc'),
            'products_desc' => __('cms.brands.sort_products_desc'),
        ];

        $filters = [
            'search' => trim((string) $request->query('search', '')),
            'status' => $request->filled('status') ? (string) $request->query('status') : '',
            'sort' => (string) $request->query('sort', 'latest'),
        ];

        if (! array_key_exists($filters['status'], $statusLabels) && $filters['status'] !== '') {
            $filters['status'] = '';
        }

        if (! array_key_exists($filters['sort'], $sortOptions)) {
            $filters['sort'] = 'latest';
        }

        $brands = $this->brandService->paginateWithFilters($filters);

        return view('admin.brands.index', [
            'brands' => $brands,
            'stats' => $this->brandService->stats(),
            'filters' => $filters,
            'statusFilters' => array_merge([
                '' => __('cms.brands.status_filter_all'),
            ], $statusLabels),
            'statusLabels' => $statusLabels,
            'sortOptions' => $sortOptions,
        ]);
    }

    public function create()
    {
        $activeLanguages = Language::where('active', 1)->get();

        return view('admin.brands.create', [
            'activeLanguages' => $activeLanguages,
            'brand' => new Brand(),
        ]);
    }

    public function store(BrandStoreRequest $request)
    {
        $data = $request->validated();
        $data['logo_url'] = $request->file('logo_url');

        $this->brandService->store($data);

        return redirect()->route('admin.brands.index')->with('success', __('cms.brands.created'));
    }

    public function edit($id)
    {
        $brand = Brand::with('translations')->findOrFail($id);

        $activeLanguages = Language::where('active', 1)->get();

        return view('admin.brands.edit', [
            'brand' => $brand,
            'activeLanguages' => $activeLanguages,
        ]);
    }

    public function update(BrandUpdateRequest $request, $id)
    {
        $data = $request->validated();
        $data['logo_url'] = $request->file('logo_url');

        $this->brandService->updateBrand($id, $data);

        return redirect()->route('admin.brands.index')->with('success', __('cms.brands.updated'));
    }

    public function destroy(Request $request, $id)
    {
        $result = $this->brandService->deleteBrand($id);

        if ($request->expectsJson()) {
            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => __('cms.brands.deleted'),
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => __('cms.brands.error_delete'),
            ]);
        }

        return redirect()
            ->route('admin.brands.index')
            ->with($result ? 'success' : 'error', $result ? __('cms.brands.deleted') : __('cms.brands.error_delete'));
    }

    public function updateStatus(BrandStatusUpdateRequest $request)
    {
        $brand = Brand::findOrFail($request->id);
        $brand->status = $request->input('status');
        $brand->save();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('cms.brands.status_updated'),
                'status' => $brand->status,
            ]);
        }

        return redirect()
            ->back()
            ->with('success', __('cms.brands.status_updated'));
    }

    protected function resolveLocalizedName(Brand $brand): string
    {
        $locale = app()->getLocale();
        $fallback = config('app.fallback_locale');

        $translation = $brand->translations->firstWhere('locale', $locale)
            ?? ($fallback ? $brand->translations->firstWhere('locale', $fallback) : null)
            ?? $brand->translations->first();

        return $translation?->name ?? $brand->slug;
    }
}
