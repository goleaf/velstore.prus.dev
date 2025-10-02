<?php

namespace App\Services\Admin;

use App\Models\Brand;
use App\Repositories\Admin\Brand\BrandRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BrandService
{
    protected BrandRepositoryInterface $brandRepository;

    public function __construct(BrandRepositoryInterface $brandRepository)
    {
        $this->brandRepository = $brandRepository;
    }

    public function getAllBrands()
    {
        return $this->brandRepository->getAll();
    }

    public function getBrandsForDataTable()
    {
        return Brand::with('translations')->select('brands.*');
    }

    public function store(array $data)
    {
        $translations = $data['translations'] ?? [];
        $primaryTranslation = $this->getPrimaryTranslation($translations);

        $slugSource = $data['slug'] ?? ($primaryTranslation['name'] ?? Str::random(8));
        $slug = $this->generateUniqueSlug($slugSource);

        $logoPath = $this->storeLogo($data['logo_url'] ?? null);

        $status = $this->normalizeStatus($data['status'] ?? 'active');

        $brand = $this->brandRepository->store([
            'slug' => $slug,
            'logo_url' => $logoPath,
            'status' => $status,
        ]);

        $this->syncTranslations($brand, $translations);

        return $brand->load('translations');
    }

    public function updateBrand(int $id, array $data)
    {
        $brand = $this->brandRepository->find($id);

        if (isset($data['logo_url']) && $data['logo_url'] instanceof UploadedFile) {
            $this->deleteLogoIfExists($brand->logo_url);
            $brand->logo_url = $this->storeLogo($data['logo_url']);
        }

        $translations = $data['translations'] ?? [];
        $primaryTranslation = $this->getPrimaryTranslation($translations);

        if (! empty($data['slug'])) {
            $brand->slug = $this->generateUniqueSlug($data['slug'], $brand->id);
        } elseif ($primaryTranslation && ! empty($primaryTranslation['name'])) {
            $brand->slug = $this->generateUniqueSlug($primaryTranslation['name'], $brand->id);
        }

        if (array_key_exists('status', $data)) {
            $brand->status = $this->normalizeStatus($data['status']);
        }

        $brand->save();

        $this->syncTranslations($brand, $translations);

        return $brand->load('translations');
    }

    public function deleteBrand(int $id)
    {
        $brand = $this->brandRepository->find($id);

        $this->deleteLogoIfExists($brand->logo_url);

        $brand->translations()->delete();

        return $brand->delete();
    }

    public function getBrandById(int $id)
    {
        return $this->brandRepository->find($id)->load('translations');
    }

    protected function storeLogo($logo): ?string
    {
        if ($logo instanceof UploadedFile) {
            return $logo->store('brands/logos', 'public');
        }

        return $logo ?: null;
    }

    protected function deleteLogoIfExists(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    protected function syncTranslations(Brand $brand, array $translations): void
    {
        foreach ($translations as $locale => $translation) {
            if (! isset($translation['name'])) {
                continue;
            }

            $brand->translations()->updateOrCreate(
                ['locale' => $locale],
                [
                    'name' => $translation['name'],
                    'description' => $translation['description'] ?? null,
                ]
            );
        }
    }

    protected function getPrimaryTranslation(array $translations): array
    {
        $currentLocale = app()->getLocale();
        if (isset($translations[$currentLocale])) {
            return $translations[$currentLocale];
        }

        $fallbackLocale = config('app.fallback_locale');
        if ($fallbackLocale && isset($translations[$fallbackLocale])) {
            return $translations[$fallbackLocale];
        }

        foreach ($translations as $translation) {
            if (is_array($translation)) {
                return $translation;
            }
        }

        return [];
    }

    protected function normalizeStatus($status): string
    {
        $status = strtolower((string) $status);

        return in_array($status, ['active', 'inactive', 'discontinued'], true)
            ? $status
            : 'active';
    }

    protected function generateUniqueSlug(string $base, ?int $ignoreId = null): string
    {
        $slug = Str::slug($base) ?: Str::random(8);
        $original = $slug;
        $counter = 1;

        while (
            Brand::where('slug', $slug)
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $original.'-'.($counter++);
        }

        return $slug;
    }
}
