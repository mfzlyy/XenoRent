<?php

namespace App\Presentation\Http\Controllers;

use App\Application\DTOs\CreateRentalData;
use App\Application\Interfaces\UnitRepositoryInterface;
use App\Application\UseCases\Rental\CreateRental;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UnitController extends Controller
{
    public function index(UnitRepositoryInterface $unitRepo)
    {
        $search = request('q', '');
        $units = Auth::user()->role === 'admin'
            ? $unitRepo->getAll(12, $search)
            : $unitRepo->getAvailable(12, $search);

        return view('units.index', compact('units'));
    }

    public function show(int $id, UnitRepositoryInterface $unitRepo)
    {
        $unit = $unitRepo->findById($id);
        if (! $unit) {
            abort(404);
        }

        return view('units.show', compact('unit'));
    }

    public function store(Request $request, int $id, CreateRental $createRental)
    {
        $data = new CreateRentalData(
            userId: Auth::id(),
            unitId: $id,
        );

        $rental = $createRental->execute($data);

        return back()->with('success', "Unit berhasil disewa. Jatuh tempo: {$rental->dueAt->format('d M Y')}");
    }
}
