<?php

namespace App\Services\Admin;

use App\Models\Attribute;
use App\Repositories\Admin\Attribute\AttributeRepositoryInterface;

class AttributeService
{
    protected AttributeRepositoryInterface $attributeRepository;

    public function __construct(AttributeRepositoryInterface $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
    }

    public function getAttributeById($id)
    {
        return $this->attributeRepository->getById($id);
    }

    public function getIndexData(array $filters = []): array
    {
        $normalisedFilters = $this->normaliseFilters($filters);

        return [
            'attributes' => $this->attributeRepository->paginateWithFilters($normalisedFilters),
            'stats' => $this->attributeRepository->getStats(),
            'filters' => $normalisedFilters,
        ];
    }

    public function createAttribute(array $data)
    {
        return $this->attributeRepository->store($data);
    }

    public function updateAttribute(Attribute $attribute, array $data)
    {
        return $this->attributeRepository->update($attribute, $data);
    }

    public function deleteAttribute(Attribute $attribute)
    {
        return $this->attributeRepository->delete($attribute);
    }

    protected function normaliseFilters(array $filters): array
    {
        $defaults = [
            'search' => '',
            'min_values' => null,
            'max_values' => null,
            'sort' => 'latest',
            'per_page' => 15,
        ];

        $filters = array_intersect_key($filters, $defaults) + $defaults;

        $filters['search'] = trim((string) $filters['search']);

        $filters['min_values'] = $filters['min_values'] !== null
            ? max(0, (int) $filters['min_values'])
            : null;

        $filters['max_values'] = $filters['max_values'] !== null
            ? max(0, (int) $filters['max_values'])
            : null;

        if (! in_array($filters['sort'], ['latest', 'oldest', 'values_desc', 'values_asc'], true)) {
            $filters['sort'] = 'latest';
        }

        $perPageOptions = [10, 15, 25, 50];

        if (! in_array((int) $filters['per_page'], $perPageOptions, true)) {
            $filters['per_page'] = 15;
        } else {
            $filters['per_page'] = (int) $filters['per_page'];
        }

        return $filters;
    }
}
