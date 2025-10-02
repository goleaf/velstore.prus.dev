<?php

namespace App\Repositories\Admin\Category;

use App\Models\Category;

interface CategoryRepositoryInterface
{
    public function all();

    public function find($id);

    public function store($data);

    public function update($id, array $data);

    public function destroy($id);

    public function storeWithTranslations(array $attributes, array $translations);

    public function updateWithTranslations(Category $category, array $attributes, array $translations);
}
