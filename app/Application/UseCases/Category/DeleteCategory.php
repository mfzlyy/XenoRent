<?php

namespace App\Application\UseCases\Category;

use App\Application\Interfaces\CategoryRepositoryInterface;
use App\Domain\Exceptions\DomainException;

class DeleteCategory
{
    public function __construct(
        private readonly CategoryRepositoryInterface $categoryRepository,
    ) {}

    public function execute(int $id): bool
    {
        $category = $this->categoryRepository->findById($id);

        if (! $category) {
            throw new DomainException('Kategori tidak ditemukan');
        }

        return $this->categoryRepository->delete($id);
    }
}
