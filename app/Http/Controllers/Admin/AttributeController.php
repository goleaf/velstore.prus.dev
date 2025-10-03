<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\Language;
use App\Services\Admin\AttributeService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Throwable;

class AttributeController extends Controller
{
    protected AttributeService $attributeService;

    public function __construct(AttributeService $attributeService)
    {
        $this->attributeService = $attributeService;
    }

    public function index(Request $request)
    {
        $indexData = $this->attributeService->getIndexData($request->all());

        return view('admin.attributes.index', [
            'attributes' => $indexData['attributes'],
            'stats' => $indexData['stats'],
            'filters' => $indexData['filters'],
        ]);
    }

    public function create()
    {
        $languages = Language::active()->get();

        return view('admin.attributes.create', compact('languages'));
    }

    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:255|unique:attributes,name',
            'values' => 'required|array|min:1',
        ];

        foreach ($request->input('values', []) as $index => $value) {
            $rules["values.$index"] = 'required|string|max:255|distinct';
        }

        if ($request->has('translations')) {
            foreach ($request->input('translations', []) as $lang => $translations) {
                if (is_array($translations)) {
                    foreach ($translations as $i => $val) {
                        $rules["translations.$lang.$i"] = 'nullable|string|max:255';
                    }
                }
            }
        }

        $validated = $request->validate($rules);

        $this->attributeService->createAttribute($validated);

        return redirect()->route('admin.attributes.index')->with('success', __('cms.attributes.success_create'));
    }

    public function edit(Attribute $attribute)
    {
        $attribute = $this->attributeService->getAttributeById($attribute->id);
        $languages = Language::active()->get();

        return view('admin.attributes.edit', compact('attribute', 'languages'));
    }

    public function update(Request $request, Attribute $attribute)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('attributes', 'name')->ignore($attribute->id)],
            'values' => 'required|array|min:1',
            'values.*' => 'required|string|max:255|distinct',
            'translations' => 'array',
            'translations.*' => 'array',
            'translations.*.*' => 'nullable|string|max:255',
        ]);

        $this->attributeService->updateAttribute($attribute, $validated);

        return redirect()->route('admin.attributes.index')->with('success', __('cms.attributes.success_update'));
    }

    public function destroy(Request $request, Attribute $attribute)
    {
        try {
            $this->attributeService->deleteAttribute($attribute);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => __('cms.attributes.success_delete'),
                ]);
            }

            return redirect()
                ->route('admin.attributes.index')
                ->with('success', __('cms.attributes.success_delete'));
        } catch (Throwable $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => __('cms.attributes.error_delete'),
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            return redirect()
                ->route('admin.attributes.index')
                ->with('error', __('cms.attributes.error_delete'));
        }
    }
}
