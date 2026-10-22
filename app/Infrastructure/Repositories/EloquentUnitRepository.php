<?php

namespace App\Infrastructure\Repositories;

use App\Application\Interfaces\UnitRepositoryInterface;
use App\Domain\Entities\Category;
use App\Domain\Entities\Unit;
use App\Domain\Enums\RentalStatus;
use App\Domain\Enums\UnitStatus;
use App\Infrastructure\Eloquent\RentalEloquent;
use App\Infrastructure\Eloquent\UnitEloquent;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentUnitRepository implements UnitRepositoryInterface
{
    public function findById(int $id): ?Unit
    {
        $model = UnitEloquent::with('categories')->find($id);

        return $model ? $this->toEntity($model) : null;
    }

    public function findByCode(string $code): ?Unit
    {
        $model = UnitEloquent::with('categories')->where('code', $code)->first();

        return $model ? $this->toEntity($model) : null;
    }

    public function getAll(int $perPage = 15, string $search = ''): LengthAwarePaginator
    {
        $query = UnitEloquent::with('categories');

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        return $query->paginate($perPage);
    }

    public function getAvailable(int $perPage = 15, string $search = ''): LengthAwarePaginator
    {
        $query = UnitEloquent::with('categories')
            ->where('status', UnitStatus::AVAILABLE->value);

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        return $query->paginate($perPage);
    }

    public function countAvailable(): int
    {
        return UnitEloquent::where('status', UnitStatus::AVAILABLE->value)->count();
    }

    public function create(Unit $unit, array $categoryIds): Unit
    {
        $model = UnitEloquent::create([
            'code' => $unit->code,
            'name' => $unit->name,
            'description' => $unit->description,
            'status' => $unit->status->value,
        ]);

        if ($categoryIds) {
            $model->categories()->attach($categoryIds);
        }

        $model->load('categories');

        return $this->toEntity($model);
    }

    public function update(Unit $unit, array $categoryIds): Unit
    {
        $model = UnitEloquent::findOrFail($unit->id);
        $model->update([
            'code' => $unit->code,
            'name' => $unit->name,
            'description' => $unit->description,
            'status' => $unit->status->value,
        ]);

        $categoryIds = array_map(
            static fn (int|string|Category $category): int => $category instanceof Category ? (int) $category->id : (int) $category,
            $categoryIds,
        );

        $model->categories()->sync($categoryIds);

        $model->load('categories');

        return $this->toEntity($model);
    }

    public function delete(int $id): bool
    {
        return UnitEloquent::destroy($id) > 0;
    }

    public function lockForUpdate(int $id): ?Unit
    {
        $model = UnitEloquent::with('categories')
            ->lockForUpdate()
            ->find($id);

        return $model ? $this->toEntity($model) : null;
    }

    public function hasActiveRentals(int $id): bool
    {
        return RentalEloquent::where('unit_id', $id)
            ->where('status', RentalStatus::ACTIVE->value)
            ->exists();
    }

    private function toEntity(UnitEloquent $model): Unit
    {
        $categories = $model->categories->map(function ($cat) {
            return new Category(
                id: $cat->id,
                name: $cat->name,
                description: $cat->description,
                createdAt: $cat->created_at?->toDateTimeImmutable(),
                updatedAt: $cat->updated_at?->toDateTimeImmutable(),
                unitsCount: $cat->pivot ? 1 : 0,
            );
        })->toArray();

        return new Unit(
            id: $model->id,
            code: $model->code,
            name: $model->name,
            description: $model->description,
            status: UnitStatus::from($model->status),
            createdAt: $model->created_at?->toDateTimeImmutable(),
            updatedAt: $model->updated_at?->toDateTimeImmutable(),
            deletedAt: $model->deleted_at?->toDateTimeImmutable(),
            categories: $categories,
        );
    }
}
