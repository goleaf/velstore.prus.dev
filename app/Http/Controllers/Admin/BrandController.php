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
use Yajra\DataTables\Facades\DataTables;

class BrandController extends Controller
{
    protected $brandService;

    public function __construct(BrandService $brandService)
    {
        $this->brandService = $brandService;
    }

    public function index()
    {
        return view('admin.brands.index');
    }

    public function getData(Request $request)
    {
        $brands = $this->brandService->getBrandsForDataTable();

        return DataTables::of($brands)
            ->addColumn('name', fn (Brand $brand) => $this->resolveLocalizedName($brand))
            ->editColumn('status', fn (Brand $brand) => $brand->status)
            ->addColumn('action', fn () => '')
            ->filterColumn('name', function ($query, $keyword) {
                $query->whereHas('translations', function ($relation) use ($keyword) {
                    $relation->where('name', 'like', "%{$keyword}%");
                });
            })
            ->toJson();
    }

    public function create()
    {
        $activeLanguages = Language::active()->get();

        return view('admin.brands.create', compact('activeLanguages'));
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

        $activeLanguages = Language::active()->get();

        return view('admin.brands.edit', compact('brand', 'activeLanguages'));
    }

    public function update(BrandUpdateRequest $request, $id)
    {
        $data = $request->validated();
        $data['logo_url'] = $request->file('logo_url');

        $this->brandService->updateBrand($id, $data);

        return redirect()->route('admin.brands.index')->with('success', __('cms.brands.updated'));
    }

    public function destroy($id)
    {
        $result = $this->brandService->deleteBrand($id);

        if ($result) {
            return response()->json([
                'success' => true,
                'message' => __('cms.brands.deleted'),
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting brand.',
            ]);
        }
    }

    public function updateStatus(BrandStatusUpdateRequest $request)
    {
        $brand = Brand::findOrFail($request->id);
        $brand->status = $request->input('status');
        $brand->save();

        return response()->json([
            'success' => true,
            'message' => __('cms.brands.status_updated'),
            'status' => $brand->status,
        ]);
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
