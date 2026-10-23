<?php

namespace App\Presentation\Http\Controllers;

use App\Application\DTOs\CreateUnitData;
use App\Application\DTOs\UpdateUnitData;
use App\Application\Interfaces\CategoryRepositoryInterface;
use App\Application\Interfaces\UnitRepositoryInterface;
use App\Application\UseCases\Unit\CreateUnit;
use App\Application\UseCases\Unit\DeleteUnit;
use App\Application\UseCases\Unit\UpdateUnit;
use App\Domain\Exceptions\DomainException;
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
            'code' => 'required|string|alpha_dash|max:20|unique:units,code',
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:1000',
            'category_ids' => 'required|array|min:1',
            'category_ids.*' => 'exists:categories,id',
        ], $this->validationMessages(), $this->validationAttributes());

        $unitData = new CreateUnitData(
            code: $data['code'],
            name: $data['name'],
            description: $data['description'] ?? null,
            categoryIds: $data['category_ids'],
        );

        $createUnit->execute($unitData);

        return back()->with('success', 'Unit berhasil ditambahkan');
    }

    public function update(Request $request, int $id, UpdateUnit $updateUnit)
    {
        $data = $request->validate([
            'code' => 'required|string|alpha_dash|max:20|unique:units,code,'.$id,
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:1000',
            'category_ids' => 'required|array|min:1',
            'category_ids.*' => 'exists:categories,id',
        ], $this->validationMessages(), $this->validationAttributes());

        $unitData = new UpdateUnitData(
            code: $data['code'],
            name: $data['name'],
            description: $data['description'] ?? null,
            categoryIds: $data['category_ids'],
        );

        $updateUnit->execute($id, $unitData);

        return back()->with('success', 'Unit berhasil diperbarui');
    }

    /** @return array<string, string> */
    private function validationMessages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'string' => ':attribute harus berupa teks.',
            'alpha_dash' => ':attribute hanya boleh berisi huruf, angka, tanda hubung, dan garis bawah.',
            'max' => ':attribute tidak boleh lebih dari :max karakter.',
            'array' => ':attribute harus berupa daftar.',
            'min' => ':attribute harus memiliki minimal :min pilihan.',
            'exists' => ':attribute yang dipilih tidak valid.',
            'category_ids.required' => 'Pilih minimal 1 kategori.',
            'category_ids.min' => 'Pilih minimal 1 kategori.',
            'code.unique' => 'Kode unit sudah dipakai',
        ];
    }

    /** @return array<string, string> */
    private function validationAttributes(): array
    {
        return [
            'code' => 'kode',
            'name' => 'nama',
            'description' => 'deskripsi',
            'category_ids' => 'kategori',
            'category_ids.*' => 'kategori',
        ];
    }

    public function destroy(int $id, DeleteUnit $deleteUnit)
    {
        try {
            $deleteUnit->execute($id);
        } catch (DomainException $exception) {
            return back()->withErrors(['unit' => $exception->getMessage()]);
        }

        return back()->with('success', 'Unit berhasil dihapus');
    }
}
