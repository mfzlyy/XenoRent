<?php

namespace App\Infrastructure\Repositories;

use App\Application\Interfaces\CategoryRepositoryInterface;
use App\Domain\Entities\Category;
use App\Infrastructure\Eloquent\CategoryEloquent;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentCategoryRepository implements CategoryRepositoryInterface
{
    public function findById(int $id): ?Category
    {
        $model = CategoryEloquent::find($id);

        return $model ? $this->toEntity($model) : null;
    }

    public function findByName(string $name): ?Category
    {
        $model = CategoryEloquent::where('name', $name)->first();

        return $model ? $this->toEntity($model) : null;
    }

    public function getAll(int $perPage = 15): LengthAwarePaginator
    {
        return CategoryEloquent::withCount('units')->paginate($perPage);
    }

    public function getAllForSelect(): array
    {
        return CategoryEloquent::orderBy('name')->pluck('name', 'id')->toArray();
    }

    public function create(Category $category): Category
    {
        $model = CategoryEloquent::create([
            'name' => $category->name,
            'description' => $category->description,
        ]);

        return $this->toEntity($model);
    }

    public function update(Category $category): Category
    {
        $model = CategoryEloquent::findOrFail($category->id);
        $model->update([
            'name' => $category->name,
            'description' => $category->description,
        ]);

        return $this->toEntity($model);
    }

    public function delete(int $id): bool
    {
        return CategoryEloquent::destroy($id) > 0;
    }

    private function toEntity(CategoryEloquent $model): Category
    {
        return new Category(
            id: $model->id,
            name: $model->name,
            description: $model->description,
            createdAt: $model->created_at?->toDateTimeImmutable(),
            updatedAt: $model->updated_at?->toDateTimeImmutable(),
            unitsCount: $model->units_count ?? 0,
        );
    }
}
