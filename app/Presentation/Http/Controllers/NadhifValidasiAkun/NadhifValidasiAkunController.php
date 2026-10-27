<?php

namespace App\Presentation\Http\Controllers\NadhifValidasiAkun;

use App\Application\UseCases\NadhifValidasiAkun\NadhifValidasiAkun;
use App\Infrastructure\Eloquent\UserEloquent;
use App\Presentation\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NadhifValidasiAkunController extends Controller
{
    public function index(): View
    {
        $pendingAccounts = UserEloquent::with('profile')
            ->where('role', 'anggota')
            ->where('is_approved', false)
            ->latest()
            ->paginate(15);

        return view('admin.NadhifValidasiAkun.index', compact('pendingAccounts'));
    }

    public function approve(int $id, NadhifValidasiAkun $NadhifValidasiAkun): RedirectResponse
    {
        if (! $NadhifValidasiAkun->NadhifValidasiAkun($id)) {
            return back()->with('error', 'Akun tidak ditemukan atau sudah divalidasi.');
        }

        return back()->with('success', 'Akun anggota disetujui. Anggota sekarang bisa masuk.');
    }
}
