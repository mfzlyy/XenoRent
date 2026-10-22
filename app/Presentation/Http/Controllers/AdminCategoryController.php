<?php

namespace App\Presentation\Http\Controllers;

use App\Application\DTOs\CreateCategoryData;
use App\Application\DTOs\UpdateCategoryData;
use App\Application\Interfaces\CategoryRepositoryInterface;
use App\Application\UseCases\Category\CreateCategory;
use App\Application\UseCases\Category\DeleteCategory;
use App\Application\UseCases\Category\UpdateCategory;
use Illuminate\Http\Request;

class AdminCategoryController extends Controller
{
    public function index(CategoryRepositoryInterface $categoryRepo)
    {
        $categories = $categoryRepo->getAll(15);

        return view('admin.categories.index', compact('categories'));
    }

    public function store(Request $request, CreateCategory $createCategory)
    {
        $data = $request->validate([
            'name' => 'required|string|max:50|unique:categories,name',
            'description' => 'nullable|string|max:500',
        ]);

        $categoryData = new CreateCategoryData(
            name: $data['name'],
            description: $data['description'],
        );

        $createCategory->execute($categoryData);

        return back()->with('success', 'Kategori berhasil ditambahkan');
    }

    public function update(Request $request, int $id, UpdateCategory $updateCategory)
    {
        $data = $request->validate([
            'name' => 'required|string|max:50|unique:categories,name,'.$id,
            'description' => 'nullable|string|max:500',
        ]);

        $categoryData = new UpdateCategoryData(
            name: $data['name'],
            description: $data['description'],
        );

        $updateCategory->execute($id, $categoryData);

        return back()->with('success', 'Kategori berhasil diperbarui');
    }

    public function destroy(int $id, DeleteCategory $deleteCategory)
    {
        $deleteCategory->execute($id);

        return back()->with('success', 'Kategori berhasil dihapus');
    }
}
