<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Models\Page;
use App\Models\PageTranslation;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class PageController extends Controller
{
    public function index()
    {
        $pages = Page::with(['translations'])->get();

        $latestUpdatedAt = $pages->max('updated_at');

        $stats = [
            'total' => $pages->count(),
            'active' => $pages->where('status', true)->count(),
            'inactive' => $pages->where('status', false)->count(),
            'last_updated' => $latestUpdatedAt ? Carbon::parse($latestUpdatedAt) : null,
        ];

        return view('admin.pages.index', compact('pages', 'stats'));
    }

    public function data(Request $request)
    {
        $pages = Page::with('translations')->select('pages.*');

        return DataTables::of($pages)
            ->addColumn('title', function (Page $page) {
                $defaultLocale = config('app.locale');
                $primaryTranslation = $page->translations
                    ->firstWhere('language_code', $defaultLocale)
                    ?? $page->translations->first();

                $title = e(optional($primaryTranslation)->title ?? __('cms.pages.untitled'));
                $meta = $page->updated_at
                    ? __('cms.pages.last_updated_on', ['date' => $page->updated_at->format('M j, Y')])
                    : __('cms.pages.last_updated_on', ['date' => '—']);

                return '<div class="d-flex flex-column">'
                    .'<span class="fw-semibold">'.$title.'</span>'
                    .'<span class="text-muted small">'.$meta.'</span>'
                    .'</div>';
            })
            ->editColumn('slug', function (Page $page) {
                return '<code>'.e($page->slug).'</code>';
            })
            ->addColumn('languages', function (Page $page) {
                if ($page->translations->isEmpty()) {
                    return '<span class="text-muted">'.__('cms.pages.no_translations').'</span>';
                }

                return $page->translations
                    ->sortBy('language_code')
                    ->map(function (PageTranslation $translation) {
                        $code = strtoupper($translation->language_code);

                        return '<span class="badge rounded-pill bg-light text-secondary border border-secondary-subtle me-1">'
                            .$code
                            .'</span>';
                    })
                    ->implode('');
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
            ->editColumn('status', function (Page $page) {
                $toggleId = 'page-status-'.$page->id;
                $checked = $page->status ? 'checked' : '';
                $label = $page->status
                    ? __('cms.pages.status_active')
                    : __('cms.pages.status_inactive');

                return '<div class="form-check form-switch mb-0">'
                    .'<input class="form-check-input js-page-status-toggle" type="checkbox" role="switch"'
                    .' id="'.$toggleId.'" data-id="'.$page->id.'" '.$checked.'>'
                    .'<label class="form-check-label small" for="'.$toggleId.'">'.$label.'</label>'
                    .'</div>';
            })
            ->editColumn('updated_at', function (Page $page) {
                return $page->updated_at ? $page->updated_at->format('M j, Y H:i') : '—';
            })
            ->filterColumn('title', function ($query, $keyword) {
                $query->whereHas('translations', function ($translationQuery) use ($keyword) {
                    $translationQuery->where('title', 'like', "%{$keyword}%");
                });
            })
            ->rawColumns(['title', 'slug', 'languages', 'status', 'action'])
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

        $page = Page::find($request->id);
        $page->status = $request->status;
        $page->save();

        return response()->json([
            'success' => true,
            'status' => (bool) $page->status,
            'message' => 'Page status updated.',
        ], 200);
    }
}
