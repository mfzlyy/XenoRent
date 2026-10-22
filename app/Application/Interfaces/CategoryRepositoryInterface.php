<?php

namespace App\Application\Interfaces;

use App\Domain\Entities\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CategoryRepositoryInterface
{
    public function findById(int $id): ?Category;

    public function findByName(string $name): ?Category;

    public function getAll(int $perPage = 15): LengthAwarePaginator;

    public function getAllForSelect(): array;

    public function create(Category $category): Category;

    public function update(Category $category): Category;

    public function delete(int $id): bool;
}
