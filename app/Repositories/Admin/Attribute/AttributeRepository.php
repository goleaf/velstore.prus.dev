<?php

namespace App\Repositories\Admin\Attribute;

use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class AttributeRepository implements AttributeRepositoryInterface
{
    public function getById($id)
    {
        return Attribute::with('values.translations')->findOrFail($id);
    }

    public function paginateWithFilters(array $filters): LengthAwarePaginator
    {
        $query = Attribute::query()
            ->with([
                'values' => fn ($relation) => $relation->orderBy('value'),
                'values.translations',
            ])
            ->withCount(['values', 'products']);

        if (! empty($filters['search'])) {
            $search = $filters['search'];

            $query->where(function (Builder $builder) use ($search) {
                $builder
                    ->where('name', 'like', "%{$search}%")
                    ->orWhereHas('values', function (Builder $valueQuery) use ($search) {
                        $valueQuery->where('value', 'like', "%{$search}%");
                    });
            });
        }

        if (! empty($filters['min_values'])) {
            $query->having('values_count', '>=', (int) $filters['min_values']);
        }

        if (! empty($filters['max_values'])) {
            $query->having('values_count', '<=', (int) $filters['max_values']);
        }

        switch ($filters['sort'] ?? 'latest') {
            case 'oldest':
                $query->orderBy('created_at');
                break;
            case 'values_desc':
                $query->orderByDesc('values_count')->orderBy('name');
                break;
            case 'values_asc':
                $query->orderBy('values_count')->orderBy('name');
                break;
            default:
                $query->orderByDesc('created_at');
                break;
        }

        return $query->paginate($filters['per_page'] ?? 15)->withQueryString();
    }

    public function getStats(): array
    {
        $totalAttributes = Attribute::count();
        $totalValues = AttributeValue::count();
        $topAttribute = Attribute::withCount('values')
            ->orderByDesc('values_count')
            ->first();

        return [
            'total' => $totalAttributes,
            'values' => $totalValues,
            'average_per_attribute' => $totalAttributes > 0
                ? round($totalValues / $totalAttributes, 1)
                : 0,
            'top_attribute' => $topAttribute
                ? [
                    'id' => $topAttribute->id,
                    'name' => $topAttribute->name,
                    'values_count' => $topAttribute->values_count,
                ]
                : null,
        ];
    }

    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {
            $values = collect($data['values'] ?? [])
                ->map(fn ($value) => trim((string) $value))
                ->filter()
                ->values();

            $translations = collect($data['translations'] ?? [])
                ->map(function ($languageValues) {
                    return collect($languageValues)
                        ->map(fn ($value) => trim((string) $value))
                        ->values();
                });

            $attribute = Attribute::create([
                'name' => trim($data['name']),
            ]);

            $values->each(function ($value, $index) use ($attribute, $translations) {
                $attributeValue = $attribute->values()->create([
                    'value' => $value,
                ]);

                $translations->each(function ($languageValues, $languageCode) use ($attributeValue, $index) {
                    $translatedValue = $languageValues[$index] ?? null;

                    if ($translatedValue !== null && $translatedValue !== '') {
                        $attributeValue->translations()->create([
                            'language_code' => $languageCode,
                            'translated_value' => $translatedValue,
                        ]);
                    }
                });
            });

            return $attribute->fresh(['values.translations']);
        });
    }

    public function update(Attribute $attribute, array $data)
    {
        return DB::transaction(function () use ($attribute, $data) {
            $values = collect($data['values'] ?? [])
                ->map(fn ($value) => trim((string) $value))
                ->filter()
                ->values();

            $translations = collect($data['translations'] ?? [])
                ->map(function ($languageValues) {
                    return collect($languageValues)
                        ->map(fn ($value) => trim((string) $value))
                        ->values();
                });

            $attribute->update(['name' => trim($data['name'])]);

            $attribute->values()->delete();

            $values->each(function ($value, $index) use ($attribute, $translations) {
                $attributeValue = $attribute->values()->create(['value' => $value]);

                $translations->each(function ($languageValues, $languageCode) use ($attributeValue, $index) {
                    $translatedValue = $languageValues[$index] ?? null;

                    if ($translatedValue !== null && $translatedValue !== '') {
                        $attributeValue->translations()->create([
                            'language_code' => $languageCode,
                            'translated_value' => $translatedValue,
                        ]);
                    }
                });
            });

            return $attribute->fresh(['values.translations']);
        });
    }

    public function delete(Attribute $attribute)
    {
        return (bool) $attribute->delete();
    }
}
