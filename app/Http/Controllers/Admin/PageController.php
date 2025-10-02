<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Models\Page;
use App\Models\PageTranslation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class PageController extends Controller
{
    public function index()
    {
        $pages = Page::with(['translations'])->get();

        return view('admin.pages.index', compact('pages'));
    }

    public function data(Request $request)
    {
        $pages = Page::with('translations')->select('pages.*');

        return DataTables::of($pages)
            ->addColumn('translated_title', function ($page) {
                return optional($page->translations->first())->title ?? '';
            })
            ->addColumn('action', function ($page) {
                $editRoute = route('admin.pages.edit', $page->id);

                return '<div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-outline-primary btn-edit-page" data-url="'.$editRoute.'">
                                <i class="bi bi-pencil-fill"></i>
                            </button>
                            <button type="button" class="btn btn-outline-danger btn-delete-page" data-id="'.$page->id.'">
                                <i class="bi bi-trash-fill"></i>
                            </button>
                        </div>';
            })
            ->editColumn('status', function ($page) {
                return $page->status
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-secondary">Inactive</span>';
            })
            ->rawColumns(['action', 'status'])
            ->make(true);
    }

    public function getPages(Request $request)
    {
        if ($request->ajax()) {
            return datatables()->of(Page::with('translations'))->make(true);
        }
    }

    public function create()
    {
        $activeLanguages = Language::where('active', true)->get();

        return view('admin.pages.create', compact('activeLanguages'));
    }

    public function store(Request $request)
    {
        $rules = [
            'translations' => 'required|array',
        ];

        foreach ($request->input('translations', []) as $lang => $data) {
            $rules["translations.$lang.title"] = 'required|string|max:255';
            $rules["translations.$lang.content"] = 'required|string|min:5';
            $rules["translations.$lang.image"] = 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048';
        }

        $request->validate($rules);

        $translations = $request->input('translations', []);

        foreach ($translations as $lang => $translation) {
            if ($request->hasFile("translations.$lang.image")) {
                $translations[$lang]['image'] = $request->file("translations.$lang.image");
            }
        }

        $defaultLang = config('app.locale');
        $title = $request->translations[$defaultLang]['title'] ?? null;

        $baseSlug = Str::slug($title);
        $slug = $baseSlug;
        $counter = 1;

        while (Page::where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$counter++;
        }

        $page = Page::create([
            'slug' => $slug,
            'status' => $request->status ?? 1,
        ]);

        foreach ($request->translations as $lang => $data) {
            $imagePath = null;

            if (isset($data['image']) && $data['image'] instanceof \Illuminate\Http\UploadedFile) {
                $imagePath = $data['image']->store('pages', 'public');
            }

            PageTranslation::create([
                'page_id' => $page->id,
                'language_code' => $lang,
                'title' => $data['title'],
                'content' => $data['content'] ?? null,
                'image_url' => $imagePath,
            ]);
        }

        return redirect()->route('admin.pages.index')->with('success', 'Page created successfully.');
    }

    public function edit($id)
    {
        $page = Page::with('translations')->findOrFail($id);
        $activeLanguages = Language::where('active', true)->get();

        return view('admin.pages.edit', compact('page', 'activeLanguages'));
    }

    public function update(Request $request, $id)
    {
        $page = Page::findOrFail($id);

        $rules = [
            'translations' => 'required|array',
        ];

        foreach ($request->input('translations', []) as $lang => $data) {
            $rules["translations.$lang.title"] = 'required|string|max:255';
            $rules["translations.$lang.content"] = 'nullable|string';
            $rules["translations.$lang.image"] = 'nullable|image|max:2048';
        }

        $request->validate($rules);

        $page->update([
            'status' => $request->status ?? 1,
        ]);

        foreach ($request->translations as $lang => $data) {
            $translation = PageTranslation::where('page_id', $page->id)->where('language_code', $lang)->first();

            $imagePath = $translation->image_url;

            if (isset($data['image']) && $data['image'] instanceof \Illuminate\Http\UploadedFile) {
                if ($imagePath) {
                    Storage::disk('public')->delete($imagePath);
                }
                $imagePath = $data['image']->store('pages', 'public');
            }

            if ($translation) {
                $translation->update([
                    'title' => $data['title'],
                    'content' => $data['content'],
                    'image_url' => $imagePath,
                ]);
            } else {
                PageTranslation::create([
                    'page_id' => $page->id,
                    'language_code' => $lang,
                    'title' => $data['title'],
                    'content' => $data['content'],
                    'image_url' => $imagePath,
                ]);
            }
        }

        return redirect()->route('admin.pages.index')->with('success', 'Page updated successfully.');
    }

    public function destroy($id)
    {
        $page = Page::findOrFail($id);
        $page->delete();

        return response()->json([
            'success' => true,
            'message' => 'Page deleted successfully.',
        ]);
    }

    public function updatePageStatus(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:pages,id',
            'status' => 'required|boolean',
        ]);

        $page = Page::findOrFail($request->id);
        $page->status = (bool) $request->boolean('status');
        $page->save();

        $page->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Page status updated successfully.',
            'data' => [
                'id' => $page->id,
                'status' => (bool) $page->status,
            ],
        ]);
    }
}
