<?php

namespace App\Application\UseCases\Category;

use App\Application\DTOs\UpdateCategoryData;
use App\Application\Interfaces\CategoryRepositoryInterface;
use App\Domain\Entities\Category;
use App\Domain\Exceptions\DomainException;

class UpdateCategory
{
    public function __construct(
        private readonly CategoryRepositoryInterface $categoryRepository,
    ) {}

    public function execute(int $id, UpdateCategoryData $data): Category
    {
        $category = $this->categoryRepository->findById($id);

        if (! $category) {
            throw new DomainException('Kategori tidak ditemukan');
        }

        $existing = $this->categoryRepository->findByName($data->name);
        if ($existing && $existing->id !== $id) {
            throw new DomainException('Nama kategori sudah digunakan');
        }

        $updatedCategory = new Category(
            id: $category->id,
            name: $data->name,
            description: $data->description,
            createdAt: $category->createdAt,
            updatedAt: new \DateTimeImmutable,
            unitsCount: $category->unitsCount,
        );

        return $this->categoryRepository->update($updatedCategory);
    }
}
