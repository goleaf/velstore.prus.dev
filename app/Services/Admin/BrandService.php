<?php

namespace App\Services\Admin;

use App\Models\Brand;
use App\Repositories\Admin\Brand\BrandRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
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

    public function paginateWithFilters(array $filters, int $perPage = 12): LengthAwarePaginator
    {
        $locale = app()->getLocale();
        $fallback = config('app.fallback_locale');

        $translationLocales = collect([$locale, $fallback])
            ->filter()
            ->unique()
            ->values()
            ->all();

        $query = Brand::query()
            ->select('brands.*')
            ->with([
                'translations' => fn ($relation) => $relation->whereIn('locale', $translationLocales),
            ])
            ->withCount([
                'products',
                'translations as translations_count',
            ]);

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function (Builder $builder) use ($search) {
                $builder
                    ->where('slug', 'like', "%{$search}%")
                    ->orWhereHas('translations', function (Builder $translationQuery) use ($search) {
                        $translationQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $this->applySort($query, $filters['sort'] ?? 'latest', $locale, $fallback);

        $perPage = (int) ($filters['per_page'] ?? $perPage);

        if ($perPage < 1) {
            $perPage = 12;
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public function stats(): array
    {
        return [
            'total' => Brand::count(),
            'active' => Brand::where('status', 'active')->count(),
            'inactive' => Brand::where('status', 'inactive')->count(),
            'discontinued' => Brand::where('status', 'discontinued')->count(),
        ];
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

    protected function applySort(Builder $query, string $sort, string $locale, ?string $fallback): void
    {
        if (in_array($sort, ['name_asc', 'name_desc'], true)) {
            $direction = $sort === 'name_asc' ? 'asc' : 'desc';

            $query->leftJoin('brand_translations as primary_translation', function ($join) use ($locale) {
                $join->on('brands.id', '=', 'primary_translation.brand_id')
                    ->where('primary_translation.locale', '=', $locale);
            });

            $orderExpression = 'COALESCE(primary_translation.name, brands.slug)';

            if ($fallback && $fallback !== $locale) {
                $query->leftJoin('brand_translations as fallback_translation', function ($join) use ($fallback) {
                    $join->on('brands.id', '=', 'fallback_translation.brand_id')
                        ->where('fallback_translation.locale', '=', $fallback);
                });

                $orderExpression = 'COALESCE(primary_translation.name, fallback_translation.name, brands.slug)';
            }

            $query->orderByRaw($orderExpression.' '.$direction);

            return;
        }

        if ($sort === 'oldest') {
            $query->orderBy('brands.created_at', 'asc');

            return;
        }

        if ($sort === 'products_desc') {
            $query->orderBy('products_count', 'desc');

            return;
        }

        $query->orderBy('brands.created_at', 'desc');
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
