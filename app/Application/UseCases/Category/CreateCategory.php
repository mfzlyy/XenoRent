<?php

namespace App\Application\UseCases\Category;

use App\Application\DTOs\CreateCategoryData;
use App\Application\Interfaces\CategoryRepositoryInterface;
use App\Domain\Entities\Category;
use App\Domain\Exceptions\DomainException;

class CreateCategory
{
    public function __construct(
        private readonly CategoryRepositoryInterface $categoryRepository,
    ) {}

    public function execute(CreateCategoryData $data): Category
    {
        if ($this->categoryRepository->findByName($data->name)) {
            throw new DomainException('Nama kategori sudah digunakan');
        }

        $category = new Category(
            id: null,
            name: $data->name,
            description: $data->description,
        );

        return $this->categoryRepository->create($category);
    }
}
