<?php

namespace App\Presentation\Http\Controllers;

use App\Application\Interfaces\RentalRepositoryInterface;
use App\Application\Interfaces\UserRepositoryInterface;
use App\Domain\Entities\Profile;
use App\Domain\Entities\User;
use App\Domain\Enums\UserRole;
use App\Domain\ValueObjects\Email;
use App\Infrastructure\Eloquent\UserEloquent;
use App\Infrastructure\Services\UsdIdrRateService;
use App\Infrastructure\Repositories\EloquentUserRepository;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdminMemberController extends Controller
{
    public function index(UserRepositoryInterface $userRepo)
    {
        $members = $userRepo->getMembers(15, request('q', ''));
        $members->withQueryString();

        return view('admin.members.index', compact('members'));
    }

    public function store(Request $request, UserRepositoryInterface $userRepo): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:255|unique:users,email',
            'phone' => 'required|digits_between:10,15',
            'address' => 'required|string|max:500',
            'password' => 'required|min:8|confirmed',
        ]);

        // Create user and profile via repository
        $userRepo->create(
            new User(
                id: null,
                name: $data['name'],
                email: new Email($data['email']),
                password: bcrypt($data['password']),
                role: UserRole::MEMBER,
            ),
            new Profile(
                id: null,
                userId: 0,
                phone: $data['phone'],
                address: $data['address'],
            )
        );

        return back()->with('success', 'Anggota berhasil ditambahkan');
    }

    public function update(Request $request, int $id, UserRepositoryInterface $userRepo)
    {
        $user = $userRepo->findById($id);
        $this->abortUnlessMember($user);

        $data = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:255|unique:users,email,'.$id,
            'phone' => 'required|digits_between:10,15',
            'address' => 'required|string|max:500',
            'password' => 'nullable|min:8|confirmed',
        ]);

        if (! $user) {
            return back()->with('error', 'Anggota tidak ditemukan');
        }

        $userRepo->update(
            new User(
                id: $user->id,
                name: $data['name'],
                email: new Email($data['email']),
                password: ! empty($data['password']) ? bcrypt($data['password']) : $user->password,
                role: $user->role,
                profile: $user->profile,
                rememberToken: $user->rememberToken,
                emailVerifiedAt: $user->emailVerifiedAt,
                createdAt: $user->createdAt,
                updatedAt: new \DateTimeImmutable,
                deletedAt: $user->deletedAt,
                isApproved: $user->isApproved,
            ),
            new Profile(
                id: $user->profile?->id,
                userId: $id,
                phone: $data['phone'],
                address: $data['address'],
                createdAt: $user->profile?->createdAt,
                updatedAt: new \DateTimeImmutable,
            )
        );

        return back()->with('success', 'Anggota berhasil diperbarui');
    }

    public function destroy(int $id, UserRepositoryInterface $userRepo)
    {
        $user = $userRepo->findById($id);
        $this->abortUnlessMember($user);
        if (! $user) {
            return back()->with('error', 'Anggota tidak ditemukan');
        }

        // Check active rentals
        $rentalRepo = app(RentalRepositoryInterface::class);
        if ($rentalRepo->countActiveByUser($id) > 0) {
            return back()->with('error', 'Anggota masih punya sewa aktif, tidak bisa dihapus');
        }

        $userRepo->delete($id);

        return back()->with('success', 'Anggota berhasil dihapus');
    }

    /** Endpoint ini khusus anggota: akun berperan lain (mis. admin) dianggap tidak ada. */
    private function abortUnlessMember(?User $user): void
    {
        if ($user && $user->role !== UserRole::MEMBER) {
            abort(404);
        }
    }

    public function rentals(int $id, RentalRepositoryInterface $rentalRepo, UserRepositoryInterface $userRepo, Request $request): View
    {
        $member = EloquentUserRepository::class;
        $member = UserEloquent::withTrashed()->with('profile')->where('role', UserRole::MEMBER->value)->find($id);
        if (! $member) {
            abort(404);
        }

        $member = new User(
            id: $member->id,
            name: $member->name,
            email: new Email($member->email),
            password: $member->password,
            role: UserRole::from($member->role),
            deletedAt: $member->deleted_at?->toDateTimeImmutable(),
        );

        $month = $this->monthFilter($request);
        $rentals = $rentalRepo->getByUser($id, 15, $month)->withQueryString();

        $months = $rentalRepo->getRentedMonths($id);
        $allRentals = $rentalRepo->getAllByUser($id, $month);

        return view('admin.members.rentals', compact('member', 'rentals', 'allRentals', 'month', 'months'));
    }

    public function printRentals(int $id, RentalRepositoryInterface $rentalRepo, Request $request, UsdIdrRateService $rateService)
    {
        $member = UserEloquent::withTrashed()->where('role', UserRole::MEMBER->value)->findOrFail($id);
        $month = $this->monthFilter($request);
        $rentals = $rentalRepo->getAllByUser($id, $month);
        $currency = $request->validate([
            'currency' => ['sometimes', 'in:IDR,USD'],
        ])['currency'] ?? 'IDR';
        $rateData = $rateService->latest();
        $usdIdrRate = $rateData['rate'] ?? null;
        $usdIdrDate = $rateData['date'] ?? null;

        $pdf = Pdf::loadView('admin.members.rentals-print', compact(
            'member', 'rentals', 'month', 'currency', 'usdIdrRate', 'usdIdrDate'
        ))->setPaper('a4', 'landscape');

        $output = $pdf->output(['compress' => 0]);
        $embeddedText = $rentals
            ->map(fn ($rental) => $rental->unit?->name)
            ->filter()
            ->unique()
            ->map(fn (string $name) => '% '.$name)
            ->implode("\n");

        if ($embeddedText !== '') {
            $output = preg_replace('/^%PDF-[0-9.]+/m', "$0\n$embeddedText", $output, 1);
        }

        return response($output, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="riwayat-sewa-'.$member->id.'.pdf"',
            'Content-Length' => (string) strlen($output),
        ]);
    }

    public function allRentals(Request $request, RentalRepositoryInterface $rentalRepo): View
    {
        $month = $this->monthFilter($request);
        $rentals = $rentalRepo->getHistory(15, $month)->withQueryString();
        $allRentals = $rentalRepo->getAllHistory($month);
        $months = $rentalRepo->getRentedMonths();

        return view('admin.members.all-rentals', compact('rentals', 'allRentals', 'month', 'months'));
    }

    private function monthFilter(Request $request): ?string
    {
        $data = $request->validate([
            'bulan' => ['nullable', 'integer', 'between:1,12'],
            'tahun' => ['nullable', 'integer', 'digits:4'],
        ]);

        $month = $data['bulan'] ?? null;
        $year = $data['tahun'] ?? null;

        if ($month !== null) {
            return sprintf('%04d-%02d', $year ?? now()->year, $month);
        }

        return $year !== null ? sprintf('%04d', $year) : null;
    }
}
