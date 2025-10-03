<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    public function index(Request $request)
    {
        $language = $request->get('lang', app()->getLocale());
        $fallback = config('app.fallback_locale');
        $location = $request->get('location');
        $includeGlobal = $request->boolean('include_global', true);

        $bannersQuery = Banner::query()
            ->active()
            ->with('translations');

        if ($location) {
            $bannersQuery->forLocation($location, $includeGlobal);
        }

        $banners = $bannersQuery
            ->ordered()
            ->get()
            ->map(function (Banner $banner) use ($language, $fallback) {
                $translation = $banner->resolveTranslation($language, $fallback);

                return [
                    'id' => $banner->id,
                    'type' => $banner->type,
                    'display_location' => $banner->display_location,
                    'priority' => $banner->priority,
                    'starts_at' => optional($banner->starts_at)->toIso8601String(),
                    'ends_at' => optional($banner->ends_at)->toIso8601String(),
                    'title' => $translation?->title ?? $banner->title,
                    'description' => $translation?->description,
                    'button_text' => $translation?->button_text,
                    'button_url' => $translation?->resolvedButtonUrl(),
                    'image_url' => $translation?->resolvedImageUrl(),
                ];
            });

        return response()->json([
            'status' => true,
            'data' => $banners,
        ]);
    }
}
