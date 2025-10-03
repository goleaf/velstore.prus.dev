<?php

namespace App\Repositories\Admin\Attribute;

use App\Models\Attribute;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface AttributeRepositoryInterface
{
    public function getById($id);

    public function paginateWithFilters(array $filters): LengthAwarePaginator;

    public function getStats(): array;

    public function store(array $data);

    public function update(Attribute $attribute, array $data);

    public function delete(Attribute $attribute);
}
