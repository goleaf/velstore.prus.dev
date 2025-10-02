<?php

namespace App\Repositories\Admin\Category;

use App\Models\Category;
use App\Models\CategoryTranslation;
use Illuminate\Support\Str;

class CategoryRepository implements CategoryRepositoryInterface
{
    private const DEFAULT_IMAGE = 'categories/default-placeholder.jpg';

    public function all()
    {
        return Category::all();
    }

    public function find($id)
    {
        return Category::findOrFail($id);
    }

    public function store($data)
    {
        $slug = \Str::slug($data['name']);

        $category = $this->create([
            'slug' => $slug,
            'name' => $data['name'],
            'description' => $data['description'],
            'status' => $data['status'] ?? true,
            'parent_category_id' => $data['parent_category_id'] ?? null,
        ]);

        return $category;
    }

    public function update($id, array $data)
    {
        $category = $this->find($id);
        $slug = \Str::slug($data['name']);

        $category->update([
            'slug' => $slug,
            'name' => $data['name'],
            'description' => $data['description'],
            'status' => $data['status'] ?? true,
            'parent_category_id' => $data['parent_category_id'] ?? null,
        ]);

        return $category;
    }

    public function destroy($id)
    {
        $category = $this->find($id);

        foreach ($category->translations as $translation) {
            if ($translation->image_url) {
                \Storage::disk('public')->delete($translation->image_url);
            }
        }

        return $category->delete();
    }

    public function storeWithTranslations(array $attributes, array $translations)
    {
        $primaryTranslation = $this->getPrimaryTranslation($translations);
        $slug = $this->generateUniqueSlug(Str::slug($primaryTranslation['name'] ?? Str::random(8)));

        $category = Category::create([
            'slug' => $slug,
            'parent_category_id' => $attributes['parent_category_id'] ?? null,
            'status' => $attributes['status'] ?? true,
        ]);

        foreach ($translations as $languageCode => $translation) {
            $imagePath = $this->resolveCategoryImage($translation);

            CategoryTranslation::create([
                'category_id' => $category->id,
                'language_code' => $languageCode,
                'name' => $translation['name'],
                'description' => $translation['description'] ?? null,
                'image_url' => $imagePath,
            ]);
        }

        return $category;
    }

    public function updateWithTranslations(Category $category, array $attributes, array $translations)
    {
        $primaryTranslation = $this->getPrimaryTranslation($translations);
        $slug = $this->generateUniqueSlug(Str::slug($primaryTranslation['name'] ?? $category->slug), $category->id);

        $category->update([
            'slug' => $slug,
            'parent_category_id' => $attributes['parent_category_id'] ?? null,
            'status' => $attributes['status'] ?? $category->status,
        ]);

        foreach ($translations as $languageCode => $translation) {
            $existingPath = $category->translations()->where('language_code', $languageCode)->value('image_url');
            $imagePath = $this->resolveCategoryImage($translation, $existingPath);

            $category->translations()->updateOrCreate(
                ['language_code' => $languageCode],
                [
                    'name' => $translation['name'],
                    'description' => $translation['description'] ?? null,
                    'image_url' => $imagePath,
                ]
            );
        }

        return $category;
    }

    private function resolveCategoryImage(array $translation, ?string $existing = null): string
    {
        if (isset($translation['image']) && $translation['image'] instanceof \Illuminate\Http\UploadedFile) {
            return $translation['image']->store('categories', 'public');
        }

        if ($existing) {
            return $existing;
        }

        return self::DEFAULT_IMAGE;
    }

    protected function generateUniqueSlug(string $baseSlug, ?int $ignoreId = null): string
    {
        $slug = $baseSlug ?: Str::random(8);
        $original = $slug;
        $counter = 1;

        while (
            Category::where('slug', $slug)
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $original.'-'.($counter++);
        }

        return $slug;
    }

    protected function getPrimaryTranslation(array $translations): array
    {
        if (isset($translations['en'])) {
            return $translations['en'];
        }

        foreach ($translations as $translation) {
            if (is_array($translation)) {
                return $translation;
            }
        }

        return [];
    }
}
