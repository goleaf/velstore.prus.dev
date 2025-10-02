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

    public function index()
    {
        return view('admin.brands.index');
    }

    public function getData(Request $request)
    {
        $brands = $this->brandService->getAllBrands();

        return datatables()
            ->of($brands)
            ->addColumn('action', function ($brand) {
                return view('admin.brands.index', compact('brand'));
            })
            ->make(true);
    }

    public function create()
    {
        return view('admin.brands.create');
    }

    public function store(BrandStoreRequest $request)
    {
        $result = $this->brandService->store($request->all());

        if ($result instanceof \Illuminate\Support\MessageBag) {
            return redirect()->back()->withErrors($result)->withInput();
        }

        return redirect()->route('admin.brands.index')->with('success', __('cms.brands.created'));
    }

    public function edit($id)
    {
        $brand = Brand::with('translations')->findOrFail($id);

        $languages = Language::active()->get();

        return view('admin.brands.edit', compact('brand', 'languages'));
    }

    public function update(BrandUpdateRequest $request, $id)
    {
        $result = $this->brandService->updateBrand($id, $request->all());

        if ($result instanceof \Illuminate\Support\MessageBag) {
            return redirect()->back()->withErrors($result)->withInput();
        }

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
        $brand = Brand::find($request->id);
        $brand->status = $request->status;
        $brand->save();

        if ($brand) {
            return response()->json([
                'success' => true,
                'message' => __('cms.brands.status_updated'),
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Brand status could not be updated.',
            ]);
        }
    }
}
