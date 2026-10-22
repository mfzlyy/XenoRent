<?php

namespace App\Presentation\Http\Controllers;

use App\Application\Interfaces\RentalRepositoryInterface;
use App\Application\Interfaces\UserRepositoryInterface;
use App\Domain\Entities\Profile;
use App\Domain\Entities\User;
use App\Domain\Enums\UserRole;
use App\Domain\ValueObjects\Email;
use App\Infrastructure\Eloquent\UserEloquent;
use App\Infrastructure\Repositories\EloquentUserRepository;
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
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:255|unique:users,email,'.$id,
            'phone' => 'required|digits_between:10,15',
            'address' => 'required|string|max:500',
            'password' => 'nullable|min:8|confirmed',
        ]);

        $user = $userRepo->findById($id);
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
        if (! $user) {
            return back()->with('error', 'Anggota tidak ditemukan');
        }

        // Check active rentals
        $rentalRepo = app(RentalRepositoryInterface::class);
        if ($rentalRepo->countActiveByUser($id) > 0) {
            return back()->with('error', 'Masih punya sewa aktif, tidak bisa dihapus');
        }

        $userRepo->delete($id);

        return back()->with('success', 'Anggota berhasil dihapus');
    }

    public function rentals(int $id, RentalRepositoryInterface $rentalRepo, UserRepositoryInterface $userRepo): View
    {
        $member = EloquentUserRepository::class;
        $member = UserEloquent::withTrashed()->with('profile')->find($id);
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

        $rentals = $rentalRepo->getByUser($id, 15);

        return view('admin.members.rentals', compact('member', 'rentals'));
    }

    public function printRentals(int $id, RentalRepositoryInterface $rentalRepo): View
    {
        $member = UserEloquent::withTrashed()->findOrFail($id);
        $rentals = $rentalRepo->getAllByUser($id);

        return view('admin.members.rentals-print', compact('member', 'rentals'));
    }
}
