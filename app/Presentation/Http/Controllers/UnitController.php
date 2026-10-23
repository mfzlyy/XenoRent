<?php

namespace App\Presentation\Http\Controllers;

use App\Application\DTOs\CreateRentalData;
use App\Application\Interfaces\UnitRepositoryInterface;
use App\Application\UseCases\Rental\CreateRental;
use App\Domain\Exceptions\DomainException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UnitController extends Controller
{
    private const PER_PAGE = 10;

    public function index(UnitRepositoryInterface $unitRepo)
    {
        $validator = Validator::make(request()->all(), [
            'q' => 'nullable|string|max:100',
        ], [
            'q.max' => 'Kata kunci maksimal 100 karakter.',
        ]);

        if ($validator->fails()) {
            // Tampilkan seluruh daftar beserta pesan error, bukan mengalihkan ke halaman lain.
            $units = $unitRepo->getAll(self::PER_PAGE);

            return view('units.index', compact('units'))->withErrors($validator);
        }

        $units = $unitRepo->getAll(self::PER_PAGE, (string) request('q', ''));

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

        try {
            $rental = $createRental->execute($data);
        } catch (DomainException $exception) {
            return back()->withErrors(['rental' => $exception->getMessage()]);
        }

        return back()->with('success', "Unit berhasil disewa. Jatuh tempo: {$rental->dueAt->format('d M Y')}");
    }
}
