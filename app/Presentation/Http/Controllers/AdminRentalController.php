<?php

namespace App\Presentation\Http\Controllers;

use App\Application\DTOs\ReturnRentalData;
use App\Application\Interfaces\RentalRepositoryInterface;
use App\Application\UseCases\Rental\ReturnRental;
use Illuminate\Http\Request;

class AdminRentalController extends Controller
{
    public function active(RentalRepositoryInterface $rentalRepo)
    {
        $rentals = $rentalRepo->getActive(15);

        return view('admin.rentals.active', compact('rentals'));
    }

    public function return(Request $request, int $id, ReturnRental $returnRental)
    {
        $data = new ReturnRentalData(rentalId: $id);
        $rental = $returnRental->execute($data);

        $fineText = $rental->fineAmount > 0
            ? 'Denda: Rp'.number_format($rental->fineAmount, 0, ',', '.')
            : 'Tidak ada denda';

        return back()->with('success', "Pengembalian diproses. {$fineText}");
    }
}
