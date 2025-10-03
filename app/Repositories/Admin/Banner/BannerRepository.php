<?php

namespace App\Repositories\Admin\Banner;

use App\Models\Banner;
use App\Models\BannerTranslation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BannerRepository implements BannerRepositoryInterface
{
    // Get all banners with translations
    public function getAllBanners(): Collection
    {
        return Banner::with('translations')->orderBy('created_at', 'desc')->get();
    }

    // Get a banner by its ID
    public function getBannerById(int $id): Banner
    {
        return Banner::findOrFail($id);
    }

    // Create a new banner
    public function createBanner(array $data): Banner
    {
        return Banner::create([
            'type' => $data['type'],
            'status' => $data['status'] ?? 1,
            'title' => $data['title'] ?? null,
            'display_location' => $data['display_location'] ?? 'home',
            'priority' => $data['priority'] ?? 0,
            'starts_at' => $data['starts_at'] ?? null,
            'ends_at' => $data['ends_at'] ?? null,
        ]);
    }

    // Update the banner
    public function updateBanner(Banner $banner, array $data): Banner
    {
        $banner->fill([
            'type' => $data['type'],
            'display_location' => $data['display_location'] ?? $banner->display_location,
            'priority' => $data['priority'] ?? $banner->priority,
            'starts_at' => $data['starts_at'] ?? $banner->starts_at,
            'ends_at' => $data['ends_at'] ?? $banner->ends_at,
        ]);

        if (array_key_exists('status', $data)) {
            $banner->status = $data['status'];
        }

        if (! empty($data['title'])) {
            $banner->title = $data['title'];
        }

        $banner->save();

        return $banner;
    }

    // Delete a banner
    public function deleteBanner(Banner $banner): bool
    {
        // Delete associated images if they exist
        $translations = BannerTranslation::where('banner_id', $banner->id)->get();
        foreach ($translations as $translation) {
            $this->deleteImage($translation->image_url);
        }

        // Delete translations
        BannerTranslation::where('banner_id', $banner->id)->delete();

        // Delete the banner
        return $banner->delete();
    }

    protected function deleteImage(?string $path): void
    {
        if (! $path) {
            return;
        }

        $normalized = Str::startsWith($path, 'public/') ? Str::after($path, 'public/') : $path;

        if (Storage::disk('public')->exists($normalized)) {
            Storage::disk('public')->delete($normalized);

            return;
        }

        if (Storage::exists($path)) {
            Storage::delete($path);
        }
    }
}
