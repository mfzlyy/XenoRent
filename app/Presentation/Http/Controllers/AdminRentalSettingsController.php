<?php

namespace App\Presentation\Http\Controllers;

use App\Application\Interfaces\RentalSettingsRepositoryInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdminRentalSettingsController extends Controller
{
    public function edit(RentalSettingsRepositoryInterface $settings): View
    {
        $maxActiveRentals = $settings->getMaxActiveRentals();

        return view('admin.settings.rentals', compact('maxActiveRentals'));
    }

    public function update(Request $request, RentalSettingsRepositoryInterface $settings): RedirectResponse
    {
        $data = $request->validate([
            'max_active_rentals' => ['required', 'integer', 'min:1', 'max:100'],
        ], [
            'max_active_rentals.min' => 'Batas sewa minimal 1 unit.',
            'max_active_rentals.max' => 'Batas sewa maksimal 100 unit.',
        ]);

        $settings->setMaxActiveRentals((int) $data['max_active_rentals']);

        return redirect()
            ->route('admin.rental-settings.edit')
            ->with('success', 'Batas sewa aktif berhasil diperbarui untuk semua anggota.');
    }
}
