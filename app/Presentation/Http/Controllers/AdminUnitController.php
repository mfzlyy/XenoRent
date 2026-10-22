<?php

namespace App\Presentation\Http\Controllers;

use App\Application\DTOs\CreateUnitData;
use App\Application\DTOs\UpdateUnitData;
use App\Application\Interfaces\CategoryRepositoryInterface;
use App\Application\Interfaces\UnitRepositoryInterface;
use App\Application\UseCases\Unit\CreateUnit;
use App\Application\UseCases\Unit\DeleteUnit;
use App\Application\UseCases\Unit\UpdateUnit;
use Illuminate\Http\Request;

class AdminUnitController extends Controller
{
    public function index(UnitRepositoryInterface $unitRepo, CategoryRepositoryInterface $categoryRepo)
    {
        $units = $unitRepo->getAll(15);
        $categories = $categoryRepo->getAllForSelect();

        return view('admin.units.index', compact('units', 'categories'));
    }

    public function store(Request $request, CreateUnit $createUnit)
    {
        $data = $request->validate([
            'code' => 'required|string|max:20|unique:units,code',
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:1000',
            'category_ids' => 'required|array|min:1',
            'category_ids.*' => 'exists:categories,id',
        ]);

        $unitData = new CreateUnitData(
            code: $data['code'],
            name: $data['name'],
            description: $data['description'],
            categoryIds: $data['category_ids'],
        );

        $createUnit->execute($unitData);

        return back()->with('success', 'Unit berhasil ditambahkan');
    }

    public function update(Request $request, int $id, UpdateUnit $updateUnit)
    {
        $data = $request->validate([
            'code' => 'required|string|max:20|unique:units,code,'.$id,
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:1000',
            'category_ids' => 'required|array|min:1',
            'category_ids.*' => 'exists:categories,id',
        ]);

        $unitData = new UpdateUnitData(
            code: $data['code'],
            name: $data['name'],
            description: $data['description'],
            categoryIds: $data['category_ids'],
        );

        $updateUnit->execute($id, $unitData);

        return back()->with('success', 'Unit berhasil diperbarui');
    }

    public function destroy(int $id, DeleteUnit $deleteUnit)
    {
        $deleteUnit->execute($id);

        return back()->with('success', 'Unit berhasil dihapus');
    }
}
