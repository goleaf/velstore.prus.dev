<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\BannerTranslation;
use App\Models\Language;
use App\Services\Admin\BannerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class BannerController extends Controller
{
    protected $bannerService;

    public function __construct(BannerService $bannerService)
    {
        $this->bannerService = $bannerService;
    }

    public function index(Request $request)
    {
        $banners = $this->bannerService->getAllBanners();

        return view('admin.banners.index', compact('banners'));
    }

    public function toggleStatus($id, Request $request)
    {
        $banner = Banner::findOrFail($id);
        $banner->status = $request->status;
        $banner->save();

        return response()->json(['message' => 'Banner status updated successfully']);
    }

    public function getData(Request $request)
    {
        $locale = app()->getLocale();
        $fallbackLocale = config('app.fallback_locale', 'en');

        $typeLabels = [
            'promotion' => __('cms.banners.promotion'),
            'sale' => __('cms.banners.sale'),
            'seasonal' => __('cms.banners.seasonal'),
            'featured' => __('cms.banners.featured'),
            'announcement' => __('cms.banners.announcement'),
        ];

        $banners = Banner::with('translations')->select('banners.*');

        return DataTables::of($banners)
            ->addColumn('title', function (Banner $banner) use ($locale, $fallbackLocale) {
                $translation = $banner->translations->firstWhere('language_code', $locale)
                    ?? $banner->translations->firstWhere('language_code', $fallbackLocale)
                    ?? $banner->translations->first();

                return $translation?->title ?? __('cms.banners.untitled');
            })
            ->addColumn('type_badge', function (Banner $banner) use ($typeLabels) {
                $label = $typeLabels[$banner->type] ?? Str::title($banner->type);

                return '<span class="badge badge-soft-primary">'.e($label).'</span>';
            })
            ->addColumn('image', function (Banner $banner) use ($locale, $fallbackLocale) {
                $translation = $banner->translations->firstWhere('language_code', $locale)
                    ?? $banner->translations->firstWhere('language_code', $fallbackLocale)
                    ?? $banner->translations->first();

                $imagePath = $translation?->image_url;

                if (! $imagePath) {
                    return '';
                }

                $resolved = $this->resolveImageUrl($imagePath);

                if (! $resolved) {
                    return '';
                }

                return '<img src="'.e($resolved).'" alt="'.e(__('cms.banners.image')).'" class="h-12 w-20 rounded-md object-cover border border-gray-200" />';
            })
            ->addColumn('status', fn (Banner $banner) => (int) $banner->status)
            ->addColumn('action', fn () => '')
            ->rawColumns(['image', 'type_badge'])
            ->make(true);
    }

    public function create()
    {
        $languages = Language::where('active', 1)->get();

        return view('admin.banners.create', compact('languages'));
    }

    public function store(Request $request)
    {
        $this->bannerService->store($request);

        return redirect()->route('admin.banners.index')->with('success', __('cms.banners.created'));
    }

    public function edit($id)
    {
        $banner = Banner::findOrFail($id);

        $languages = Language::where('active', 1)->get();

        $translations = BannerTranslation::where('banner_id', $banner->id)
            ->get()
            ->keyBy('language_code');

        return view('admin.banners.edit', compact('banner', 'languages', 'translations'));
    }

    public function update(Request $request, $id)
    {
        $this->bannerService->update($request, $id);

        return redirect()->route('admin.banners.index')->with('success', __('cms.banners.updated'));
    }

    public function destroy($id)
    {
        try {
            $this->bannerService->delete($id);

            return response()->json([
                'success' => true,
                'message' => __('cms.banners.deleted'),
            ]);
        } catch (\Exception $e) {
            \Log::error("Error deleting banner with ID {$id}: ".$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the banner.',
            ]);
        }
    }

    public function updateStatus(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|exists:banners,id',
            'status' => 'required|in:0,1',
        ]);

        try {
            $banner = Banner::findOrFail($request->id);

            $banner->status = $request->status;
            $banner->save();

            return response()->json([
                'success' => true,
                'message' => __('cms.banners.status_updated'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating banner status.',
            ]);
        }
    }

    protected function resolveImageUrl(string $path): ?string
    {
        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        $normalized = Str::startsWith($path, 'public/') ? Str::after($path, 'public/') : $path;

        if (Storage::disk('public')->exists($normalized)) {
            return Storage::disk('public')->url($normalized);
        }

        if (Storage::exists($path)) {
            return Storage::url($path);
        }

        return asset($normalized);
    }
}
