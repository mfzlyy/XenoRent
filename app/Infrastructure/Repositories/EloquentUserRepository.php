<?php

namespace App\Infrastructure\Repositories;

use App\Application\Interfaces\UserRepositoryInterface;
use App\Domain\Entities\Profile;
use App\Domain\Entities\User;
use App\Domain\Enums\UserRole;
use App\Domain\ValueObjects\Email;
use App\Infrastructure\Eloquent\ProfileEloquent;
use App\Infrastructure\Eloquent\UserEloquent;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentUserRepository implements UserRepositoryInterface
{
    public function findById(int $id): ?User
    {
        $model = UserEloquent::with('profile')->find($id);

        return $model ? $this->toEntity($model) : null;
    }

    public function lockForUpdate(int $id): ?User
    {
        $model = UserEloquent::with('profile')->lockForUpdate()->find($id);

        return $model ? $this->toEntity($model) : null;
    }

    public function findByEmail(string $email): ?User
    {
        $model = UserEloquent::with('profile')->where('email', $email)->first();

        return $model ? $this->toEntity($model) : null;
    }

    public function create(User $user, Profile $profile): User
    {
        $model = UserEloquent::create([
            'name' => $user->name,
            'email' => $user->email->value,
            'password' => $user->password,
            'role' => $user->role->value,
        ]);

        $profileModel = ProfileEloquent::create([
            'user_id' => $model->id,
            'phone' => $profile->phone,
            'address' => $profile->address,
        ]);

        $model->load('profile');

        return $this->toEntity($model);
    }

    public function update(User $user, Profile $profile): User
    {
        $model = UserEloquent::findOrFail($user->id);
        $model->update([
            'name' => $user->name,
            'email' => $user->email->value,
            'password' => $user->password,
            'role' => $user->role->value,
        ]);

        ProfileEloquent::updateOrCreate(
            ['user_id' => $model->id],
            ['phone' => $profile->phone, 'address' => $profile->address]
        );

        $model->load('profile');

        return $this->toEntity($model);
    }

    public function delete(int $id): bool
    {
        return UserEloquent::destroy($id) > 0;
    }

    public function getMembers(int $perPage = 15, string $search = ''): LengthAwarePaginator
    {
        $query = UserEloquent::with('profile')
            ->withCount(['rentals as active_rentals_count' => fn ($q) => $q->where('status', 'disewa')])
            ->where('role', UserRole::MEMBER->value);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return $query->paginate($perPage);
    }

    public function countMembers(): int
    {
        return UserEloquent::where('role', UserRole::MEMBER->value)->count();
    }

    private function toEntity(UserEloquent $model): User
    {
        $profile = null;
        if ($model->profile) {
            $profile = new Profile(
                id: $model->profile->id,
                userId: $model->profile->user_id,
                phone: $model->profile->phone,
                address: $model->profile->address,
                createdAt: $model->profile->created_at?->toDateTimeImmutable(),
                updatedAt: $model->profile->updated_at?->toDateTimeImmutable(),
            );
        }

        return new User(
            id: $model->id,
            name: $model->name,
            email: new Email($model->email),
            password: $model->password,
            role: UserRole::from($model->role),
            profile: $profile,
            rememberToken: $model->remember_token,
            emailVerifiedAt: $model->email_verified_at?->toDateTimeImmutable(),
            createdAt: $model->created_at?->toDateTimeImmutable(),
            updatedAt: $model->updated_at?->toDateTimeImmutable(),
            deletedAt: $model->deleted_at?->toDateTimeImmutable(),
        );
    }
}
