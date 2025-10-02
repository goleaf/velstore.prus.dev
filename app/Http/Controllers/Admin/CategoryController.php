<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryStatusUpdateRequest;
use App\Http\Requests\Admin\CategoryStoreRequest;
use App\Http\Requests\Admin\CategoryUpdateRequest;
use App\Models\Category;
use App\Models\Language;
use App\Services\Admin\CategoryService;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    protected $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    public function index(Request $request)
    {
        $filters = $request->only(['search', 'status', 'parent']);

        $data = $this->categoryService->getIndexData($filters);

        return view('admin.categories.index', $data);
    }

    public function getCategories(Request $request)
    {
        if ($request->ajax()) {
            return $this->categoryService->getCategoriesForDataTable($request);
        }
    }

    public function create(Request $request)
    {
        $parentOptions = $this->categoryService->getParentOptions();
        $selectedParent = $request->filled('parent') ? (int) $request->input('parent') : null;

        return view('admin.categories.create', [
            'parentOptions' => $parentOptions,
            'selectedParent' => $selectedParent,
        ]);
    }

    public function store(CategoryStoreRequest $request)
    {
        $translations = $request->input('translations');
        $attributes = [
            'status' => $request->boolean('status'),
            'parent_category_id' => $request->input('parent_category_id'),
        ];
        foreach ($translations as $languageCode => $translation) {
            if ($request->hasFile("translations.$languageCode.image")) {
                $translations[$languageCode]['image'] = $request->file("translations.$languageCode.image");
            }
        }

        $result = $this->categoryService->store($attributes, $translations);

        if ($result instanceof \Illuminate\Support\MessageBag) {
            return redirect()->back()->withErrors($result)->withInput();
        }

        return redirect()->route('admin.categories.index')->with('success', __('cms.categories.created'));
    }

    public function show(string $id)
    {
        //
    }

    public function edit(string $id)
    {
        $category = Category::with('translations')->findOrFail($id);

        $activeLanguages = Language::where('active', true)->get();
        $parentOptions = $this->categoryService->getParentOptions($category->id);

        return view('admin.categories.edit', [
            'category' => $category,
            'activeLanguages' => $activeLanguages,
            'parentOptions' => $parentOptions,
        ]);
    }

    public function update(CategoryUpdateRequest $request, $id)
    {
        $translations = $request->all()['translations'];
        $attributes = [
            'status' => $request->boolean('status'),
            'parent_category_id' => $request->input('parent_category_id'),
        ];

        foreach ($translations as $languageCode => $translation) {
            if ($request->hasFile("translations.$languageCode.image")) {
                $translations[$languageCode]['image'] = $request->file("translations.$languageCode.image");
            }
        }

        $this->categoryService->update($id, $attributes, $translations);

        return redirect()->route('admin.categories.index')->with('success', __('cms.categories.updated'));
    }

    public function destroy($id)
    {
        $result = $this->categoryService->destroy($id);

        if ($result) {
            return response()->json([
                'success' => true,
                'message' => __('cms.categories.deleted'),
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting category.',
            ]);
        }
    }

    public function updateCategoryStatus(CategoryStatusUpdateRequest $request)
    {
        $category = Category::find($request->id);
        $category->status = $request->status;
        $category->save();

        if ($category) {
            return response()->json([
                'success' => true,
                'message' => __('cms.categories.status_updated'),
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Category status could not be updated.',
            ]);
        }
    }
}
