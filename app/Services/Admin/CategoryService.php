<?php

namespace App\Services\Admin;

use App\Models\Category;
use App\Repositories\Admin\Category\CategoryRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\DataTables;

class CategoryService
{
    protected $categoryRepository;

    public function __construct(CategoryRepositoryInterface $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    public function getCategoriesForDataTable($request)
    {
        $categories = $this->categoryRepository->all()->load('translations');

        return DataTables::of($categories)
            ->addColumn('name', function ($category) {
                $translation = $category->translations->firstWhere('language_code', 'en');

                return $translation ? $translation->name : 'No name available';
            })
            ->addColumn('description', function ($category) {
                $translation = $category->translations->firstWhere('language_code', 'en');

                return $translation ? $translation->description : 'No description available';
            })
            ->addColumn('action', function ($category) {
                return '
                <a href="'.route('admin.categories.edit', $category->id).'" class="btn btn-primary btn-sm">Edit</a>
                <form action="'.route('admin.categories.destroy', $category->id).'" method="POST" class="d-inline" onsubmit="return confirm(\'Are you sure you want to delete this category?\');">
                    '.csrf_field().'
                    '.method_field('DELETE').'
                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                </form>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function getIndexData(array $filters = []): array
    {
        $filters = $this->normalizeFilters($filters);

        $categories = Category::with(['translation', 'translations'])
            ->withCount('products')
            ->orderBy('parent_category_id')
            ->orderBy('id')
            ->get();

        $stats = [
            'total' => $categories->count(),
            'active' => $categories->where('status', true)->count(),
            'inactive' => $categories->where('status', false)->count(),
        ];

        $tree = $this->buildTree(
            $filters['parent'],
            $categories,
            $filters,
            [],
            true
        );

        $flattened = $this->flattenTree($tree);

        $parentOptions = $this->buildParentOptions($categories);

        return [
            'categories' => $flattened,
            'stats' => $stats,
            'filters' => $filters,
            'parentOptions' => $parentOptions,
        ];
    }

    public function getParentOptions(?int $excludeCategoryId = null): array
    {
        $categories = Category::with(['translation', 'translations'])
            ->orderBy('parent_category_id')
            ->orderBy('id')
            ->get();

        return $this->buildParentOptions($categories, $excludeCategoryId);
    }

    public function store(array $attributes, array $translations)
    {
        $validator = Validator::make($translations, [
            '*.name' => 'required|string|max:255',
            '*.description' => 'nullable|string',
            '*.image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10000',
        ]);

        if ($validator->fails()) {
            return $validator->errors();
        }

        $attributes['status'] = array_key_exists('status', $attributes)
            ? (bool) $attributes['status']
            : true;
        $attributes['parent_category_id'] = $attributes['parent_category_id'] ?: null;

        return $this->categoryRepository->storeWithTranslations($attributes, $translations);
    }

    public function update(int $id, array $attributes, array $translations)
    {
        $category = Category::with('translations')->findOrFail($id);

        $validator = Validator::make($translations, [
            '*.name' => 'required|string|max:255',
            '*.description' => 'nullable|string',
            '*.image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10000',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $attributes['status'] = array_key_exists('status', $attributes)
            ? (bool) $attributes['status']
            : (bool) $category->status;
        $attributes['parent_category_id'] = $attributes['parent_category_id'] ?: null;

        if ($attributes['parent_category_id']) {
            $allCategories = Category::select('id', 'parent_category_id')->get();
            $descendantIds = $this->collectDescendantIds($category->id, $allCategories);

            if (in_array($attributes['parent_category_id'], $descendantIds, true)) {
                throw ValidationException::withMessages([
                    'parent_category_id' => __('validation.not_in', ['attribute' => 'parent category']),
                ]);
            }
        }

        return $this->categoryRepository->updateWithTranslations($category, $attributes, $translations);
    }

    public function destroy($id)
    {
        return $this->categoryRepository->destroy($id);
    }

    public function find($id)
    {
        return $this->categoryRepository->find($id);
    }

    protected function normalizeFilters(array $filters): array
    {
        $defaults = [
            'search' => '',
            'status' => '',
            'parent' => null,
        ];

        $filters = array_merge($defaults, array_filter($filters, fn ($value) => $value !== null));

        $filters['search'] = trim((string) $filters['search']);
        $filters['status'] = in_array($filters['status'], ['active', 'inactive'], true)
            ? $filters['status']
            : '';
        $filters['parent'] = $filters['parent'] ? (int) $filters['parent'] : null;

        return $filters;
    }

    protected function buildParentOptions(Collection $categories, ?int $excludeCategoryId = null): array
    {
        $excludedIds = [];

        if ($excludeCategoryId) {
            $excludedIds = $this->collectDescendantIds($excludeCategoryId, $categories);
        }

        $tree = $this->buildTree(null, $categories, $this->normalizeFilters([]), $excludedIds, false);
        $flat = $this->flattenTree($tree);

        return collect($flat)
            ->map(function (array $node) {
                return [
                    'id' => $node['category']->id,
                    'name' => str_repeat('— ', $node['depth']).$node['name'],
                ];
            })
            ->values()
            ->all();
    }

    protected function buildTree(?int $rootId, Collection $categories, array $filters, array $excludedIds, bool $applyFilters): array
    {
        if ($rootId !== null) {
            $rootCategory = $categories->firstWhere('id', $rootId);

            if (! $rootCategory) {
                return [];
            }

            $node = $this->buildNodeFromCategory($rootCategory, $categories, $filters, $excludedIds, $applyFilters);

            return $node ? [$node] : [];
        }

        return $this->buildTreeForParent(null, $categories, $filters, $excludedIds, $applyFilters);
    }

    protected function buildTreeForParent(?int $parentId, Collection $categories, array $filters, array $excludedIds, bool $applyFilters): array
    {
        return $categories
            ->where('parent_category_id', $parentId)
            ->sortBy(function (Category $category) {
                return Str::lower($this->getCategoryDisplayName($category));
            })
            ->map(function (Category $category) use ($categories, $filters, $excludedIds, $applyFilters) {
                return $this->buildNodeFromCategory($category, $categories, $filters, $excludedIds, $applyFilters);
            })
            ->filter()
            ->values()
            ->all();
    }

    protected function buildNodeFromCategory(Category $category, Collection $categories, array $filters, array $excludedIds, bool $applyFilters): ?array
    {
        if (in_array($category->id, $excludedIds, true)) {
            return null;
        }

        $children = $this->buildTreeForParent($category->id, $categories, $filters, $excludedIds, $applyFilters);
        $matches = $applyFilters ? $this->categoryMatchesFilters($category, $filters) : true;

        if ($applyFilters && ! $matches && empty($children)) {
            return null;
        }

        return [
            'category' => $category,
            'children' => $children,
        ];
    }

    protected function categoryMatchesFilters(Category $category, array $filters): bool
    {
        if ($filters['status'] === 'active' && ! (bool) $category->status) {
            return false;
        }

        if ($filters['status'] === 'inactive' && (bool) $category->status) {
            return false;
        }

        if ($filters['search'] !== '') {
            $search = Str::lower($filters['search']);

            $nameMatches = $category->translations
                ->pluck('name')
                ->filter()
                ->map(fn ($name) => Str::lower($name))
                ->contains(fn ($name) => Str::contains($name, $search));

            $slugMatches = Str::contains(Str::lower($category->slug ?? ''), $search);

            if (! $nameMatches && ! $slugMatches) {
                return false;
            }
        }

        return true;
    }

    protected function flattenTree(array $nodes, int $depth = 0): array
    {
        $flat = [];

        foreach ($nodes as $node) {
            $category = $node['category'];
            $flat[] = [
                'category' => $category,
                'name' => $this->getCategoryDisplayName($category),
                'depth' => $depth,
                'children_count' => count($node['children']),
            ];

            $flat = array_merge($flat, $this->flattenTree($node['children'], $depth + 1));
        }

        return $flat;
    }

    protected function getCategoryDisplayName(Category $category): string
    {
        $preferred = optional($category->translation)->name;

        if ($preferred) {
            return $preferred;
        }

        return $category->translations->pluck('name')->filter()->first() ?? $category->slug ?? '—';
    }

    protected function collectDescendantIds(int $categoryId, Collection $categories): array
    {
        $ids = [$categoryId];

        foreach ($categories->where('parent_category_id', $categoryId) as $child) {
            $ids = array_merge($ids, $this->collectDescendantIds($child->id, $categories));
        }

        return array_unique($ids);
    }
}

