<?php

namespace App\Presentation\Http\Controllers;

use App\Application\Interfaces\RentalRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class RentalController extends Controller
{
    public function mine(RentalRepositoryInterface $rentalRepo)
    {
        $rentals = $rentalRepo->getByUser(Auth::id(), 15);

        return view('rentals.mine', compact('rentals'));
    }
}
