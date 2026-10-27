<?php

use App\Presentation\Http\Controllers\AdminCategoryController;
use App\Presentation\Http\Controllers\AdminMemberController;
use App\Presentation\Http\Controllers\AdminResourceController;
use App\Presentation\Http\Controllers\AdminRentalController;
use App\Presentation\Http\Controllers\AdminRentalSettingsController;
use App\Presentation\Http\Controllers\AdminUnitController;
use App\Presentation\Http\Controllers\AuthController;
use App\Presentation\Http\Controllers\CurrencyRateController;
use App\Presentation\Http\Controllers\DashboardController;
use App\Presentation\Http\Controllers\MediaGuideController;
use App\Presentation\Http\Controllers\NadhifValidasiAkun\NadhifValidasiAkunController;
use App\Presentation\Http\Controllers\ProfileController;
use App\Presentation\Http\Controllers\RentalController;
use App\Presentation\Http\Controllers\UnitController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('login'));

// Auth
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.store');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.store');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Profile (auth)
Route::middleware('auth')->group(function () {
    Route::get('/currency/usd-idr', [CurrencyRateController::class, 'show'])->name('currency.usd-idr');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

// Dashboard
Route::middleware('auth')->get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::middleware(['auth', 'role:anggota,admin'])->get('/panduan-media', [MediaGuideController::class, 'index'])->name('media.guide');

// Member area
Route::middleware(['auth', 'role:anggota,admin'])->prefix('units')->name('units.')->group(function () {
    Route::get('/', [UnitController::class, 'index'])->name('index');
    Route::get('/{unit}', [UnitController::class, 'show'])->name('show');
});

Route::middleware(['auth', 'role:anggota'])->prefix('units')->name('rentals.')->group(function () {
    Route::post('/{unit}/rent', [UnitController::class, 'store'])->name('store');
});

Route::middleware(['auth', 'role:anggota'])->prefix('rentals')->name('rentals.')->group(function () {
    Route::get('/mine', [RentalController::class, 'mine'])->name('mine');
});

// Admin area
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('units', AdminUnitController::class)->except(['show']);
    Route::resource('categories', AdminCategoryController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::get('members', [AdminMemberController::class, 'index'])->name('members.index');
    Route::post('members', [AdminMemberController::class, 'store'])->name('members.store');
    Route::put('members/{id}', [AdminMemberController::class, 'update'])->name('members.update');
    Route::delete('members/{id}', [AdminMemberController::class, 'destroy'])->name('members.destroy');
    Route::get('members/rentals', [AdminMemberController::class, 'allRentals'])->name('members.rentals.all');
    Route::get('members/{id}/rentals', [AdminMemberController::class, 'rentals'])->name('members.rentals');
    Route::get('members/{id}/rentals/print', [AdminMemberController::class, 'printRentals'])->name('members.rentals.print');
    Route::get('validasi-register', [NadhifValidasiAkunController::class, 'index'])->name('nadhif-validasi-akun.index');
    Route::post('validasi-register/{id}/approve', [NadhifValidasiAkunController::class, 'approve'])->name('nadhif-validasi-akun.approve');
    Route::get('rentals/active', [AdminRentalController::class, 'active'])->name('rentals.active');
    Route::get('resources', [AdminResourceController::class, 'index'])->name('resources.index');
    Route::get('settings/rentals', [AdminRentalSettingsController::class, 'edit'])->name('rental-settings.edit');
    Route::put('settings/rentals', [AdminRentalSettingsController::class, 'update'])->name('rental-settings.update');
    Route::post('rentals/{rental}/return', [AdminRentalController::class, 'return'])->name('rentals.return');
});
