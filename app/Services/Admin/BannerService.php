<?php

namespace App\Services\Admin;

use App\Models\BannerTranslation;
use App\Models\Language;
use App\Repositories\Admin\Banner\BannerRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BannerService
{
    protected $bannerRepository;

    public function __construct(BannerRepositoryInterface $bannerRepository)
    {
        $this->bannerRepository = $bannerRepository;
    }

    public function getAllBanners()
    {
        return $this->bannerRepository->getAllBanners();
    }

    public function store(Request $request)
    {
        $activeLanguages = Language::active()->pluck('code')->toArray();
        $defaultLocale = $this->resolveDefaultLocale($activeLanguages);

        $rules = [
            'type' => 'required|in:promotion,sale,seasonal,featured,announcement',
            'display_location' => 'required|in:home,shop,category,product,global',
            'priority' => 'nullable|integer|min:0|max:1000',
            'status' => 'required|boolean',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
        ];

        foreach ($activeLanguages as $code) {
            $rules["languages.$code.title"] = 'required|string|max:255';
            $rules["languages.$code.description"] = 'required|string|min:3';
            $rules["languages.$code.button_text"] = 'nullable|string|max:255';
            $rules["languages.$code.button_url"] = 'nullable|string|max:2048';

            $rules["languages.$code.image"] = ($code === $defaultLocale ? 'required' : 'nullable')
                .'|image|mimes:jpeg,png,jpg,gif,svg,webp|max:10000';
        }

        $validated = $request->validate($rules);

        $defaultTranslation = $request->input("languages.$defaultLocale", []);

        $banner = $this->bannerRepository->createBanner([
            'type' => $validated['type'],
            'status' => (int) $validated['status'],
            'display_location' => $validated['display_location'],
            'priority' => isset($validated['priority']) ? (int) $validated['priority'] : 0,
            'starts_at' => $validated['starts_at'] ?? null,
            'ends_at' => $validated['ends_at'] ?? null,
            'title' => $defaultTranslation['title'] ?? null,
        ]);

        $defaultImagePath = null;
        if ($request->hasFile("languages.$defaultLocale.image")) {
            $defaultImagePath = $this->storeImage($request->file("languages.$defaultLocale.image"));
        }

        foreach ($activeLanguages as $code) {
            $langInput = $request->input("languages.$code");

            if (! $langInput) {
                continue;
            }

            $imagePath = $defaultImagePath;

            if ($request->hasFile("languages.$code.image")) {
                $imagePath = $this->storeImage($request->file("languages.$code.image"));
            }

            if (! $imagePath) {
                continue;
            }

            BannerTranslation::create([
                'banner_id' => $banner->id,
                'language_code' => $code,
                'title' => $langInput['title'],
                'description' => $langInput['description'],
                'button_text' => $langInput['button_text'] ?? null,
                'button_url' => $langInput['button_url'] ?? null,
                'image_url' => $imagePath,
            ]);
        }

        return $banner;
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'type' => 'required|in:promotion,sale,seasonal,featured,announcement',
            'display_location' => 'required|in:home,shop,category,product,global',
            'priority' => 'nullable|integer|min:0|max:1000',
            'status' => 'required|boolean',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'languages.*.language_code' => 'required|string',
            'languages.*.title' => 'required|string|max:255',
            'languages.*.description' => 'required|string|min:3',
            'languages.*.button_text' => 'nullable|string|max:255',
            'languages.*.button_url' => 'nullable|string|max:2048',
            'languages.*.image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:10000',
        ]);

        $banner = $this->bannerRepository->getBannerById($id);
        $languagesInput = $request->input('languages', []);
        $defaultLocale = config('app.locale', 'en');
        $defaultLanguageData = collect($languagesInput)->firstWhere('language_code', $defaultLocale)
            ?? collect($languagesInput)->first();

        $this->bannerRepository->updateBanner($banner, [
            'type' => $validated['type'],
            'status' => (int) $request->boolean('status'),
            'display_location' => $validated['display_location'],
            'priority' => isset($validated['priority']) ? (int) $validated['priority'] : $banner->priority,
            'starts_at' => $validated['starts_at'] ?? null,
            'ends_at' => $validated['ends_at'] ?? null,
            'title' => $defaultLanguageData['title'] ?? $banner->title,
        ]);

        $existingTranslations = BannerTranslation::where('banner_id', $banner->id)
            ->get()
            ->keyBy('language_code');

        $defaultImagePath = optional($existingTranslations->get($defaultLocale))->image_url;

        foreach ($languagesInput as $index => $languageData) {
            $languageCode = $languageData['language_code'] ?? null;

            if (! $languageCode) {
                continue;
            }

            $translation = $existingTranslations->get($languageCode);
            $uploadedImage = $languageData['image'] ?? $request->file("languages.$index.image");

            if ($translation) {
                if ($uploadedImage) {
                    $this->deleteImage($translation->image_url);
                    $translation->image_url = $this->storeImage($uploadedImage);
                }

                $translation->title = $languageData['title'];
                $translation->description = $languageData['description'];
                $translation->button_text = $languageData['button_text'] ?? null;
                $translation->button_url = $languageData['button_url'] ?? null;
                $translation->save();

                if ($languageCode === $defaultLocale) {
                    $defaultImagePath = $translation->image_url;
                }

                continue;
            }

            $imagePath = null;

            if ($uploadedImage) {
                $imagePath = $this->storeImage($uploadedImage);
            } elseif ($languageCode !== $defaultLocale) {
                $imagePath = $defaultImagePath;
            }

            if (! $imagePath) {
                continue;
            }

            $createdTranslation = BannerTranslation::create([
                'banner_id' => $banner->id,
                'language_code' => $languageCode,
                'title' => $languageData['title'],
                'description' => $languageData['description'],
                'button_text' => $languageData['button_text'] ?? null,
                'button_url' => $languageData['button_url'] ?? null,
                'image_url' => $imagePath,
            ]);

            if ($languageCode === $defaultLocale) {
                $defaultImagePath = $createdTranslation->image_url;
            }
        }
    }

    public function delete(int $id)
    {
        $banner = $this->bannerRepository->getBannerById($id);
        $this->bannerRepository->deleteBanner($banner);
    }

    protected function storeImage($file): string
    {
        return $file->store('banner_images', 'public');
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

    protected function resolveDefaultLocale(array $activeLanguages): string
    {
        $defaultLocale = config('app.locale', 'en');

        if (in_array($defaultLocale, $activeLanguages, true)) {
            return $defaultLocale;
        }

        return $activeLanguages[0] ?? $defaultLocale;
    }
}
