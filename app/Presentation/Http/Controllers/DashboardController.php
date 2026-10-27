<?php

namespace App\Presentation\Http\Controllers;

use App\Application\Interfaces\RentalRepositoryInterface;
use App\Application\Interfaces\RentalSettingsRepositoryInterface;
use App\Application\Interfaces\UnitRepositoryInterface;
use App\Application\Interfaces\UserRepositoryInterface;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(
        RentalRepositoryInterface $rentalRepo,
        RentalSettingsRepositoryInterface $rentalSettings,
        UnitRepositoryInterface $unitRepo,
        UserRepositoryInterface $userRepo,
    ): View {
        $user = Auth::user();

        if ($user->role === 'admin') {
            return view('dashboard', [
                'availableUnits' => $unitRepo->countAvailable(),
                'activeRentals' => $rentalRepo->countActive(),
                'memberCount' => $userRepo->countMembers(),
                'upcomingRentals' => $rentalRepo->getActive(5),
            ]);
        }

        $activeRentalCount = $rentalRepo->countActiveByUser($user->id);
        $rentalLimit = $rentalSettings->getMaxActiveRentals();
        $myRentals = $rentalRepo->getByUser($user->id, 5);

        return view('dashboard', compact('activeRentalCount', 'rentalLimit', 'myRentals'));
    }
}
