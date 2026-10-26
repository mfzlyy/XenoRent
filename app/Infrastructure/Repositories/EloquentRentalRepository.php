<?php

namespace App\Infrastructure\Repositories;

use App\Application\Interfaces\RentalRepositoryInterface;
use App\Domain\Entities\Rental;
use App\Domain\Entities\Unit;
use App\Domain\Entities\User;
use App\Domain\Enums\RentalStatus;
use App\Domain\Enums\UnitStatus;
use App\Domain\Enums\UserRole;
use App\Domain\ValueObjects\Email;
use App\Infrastructure\Eloquent\RentalEloquent;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class EloquentRentalRepository implements RentalRepositoryInterface
{
    public function findById(int $id): ?Rental
    {
        $model = RentalEloquent::with(['user', 'unit'])->find($id);

        return $model ? $this->toEntity($model) : null;
    }

    public function getByUser(int $userId, int $perPage = 15, ?string $month = null): LengthAwarePaginator
    {
        return $this->userRentalsQuery($userId, $month)->paginate($perPage);
    }

    public function getAllByUser(int $userId, ?string $month = null): Collection
    {
        return $this->userRentalsQuery($userId, $month)->get();
    }

    public function getHistory(int $perPage = 15, ?string $month = null): LengthAwarePaginator
    {
        return $this->userRentalsQuery(null, $month)->paginate($perPage);
    }

    public function getAllHistory(?string $month = null): Collection
    {
        return $this->userRentalsQuery(null, $month)->get();
    }

    public function getRentedMonths(?int $userId = null): array
    {
        return RentalEloquent::query()
            ->when($userId !== null, fn ($q) => $q->where('user_id', $userId))
            ->pluck('rented_at')
            ->map(fn ($date) => Carbon::parse($date)->format('Y-m'))
            ->unique()
            ->sortDesc()
            ->values()
            ->all();
    }

    private function userRentalsQuery(?int $userId = null, ?string $month = null): Builder
    {
        $query = RentalEloquent::with(['user' => fn ($q) => $q->withTrashed(), 'unit'])
            ->latest('rented_at');

        if ($userId !== null) {
            $query->where('user_id', $userId);
        }

        if ($month !== null) {
            $isYear = strlen($month) === 4;
            $start = $isYear
                ? Carbon::createFromFormat('!Y', $month)->startOfYear()
                : Carbon::createFromFormat('!Y-m', $month)->startOfMonth();
            $end = $isYear ? $start->copy()->addYear() : $start->copy()->addMonth();

            $query->where('rented_at', '>=', $start->toDateString())
                ->where('rented_at', '<', $end->toDateString());
        }

        return $query;
    }

    public function getActive(int $perPage = 15): LengthAwarePaginator
    {
        return RentalEloquent::with(['user', 'unit'])
            ->where('status', RentalStatus::ACTIVE->value)
            ->latest('rented_at')
            ->paginate($perPage);
    }

    public function countActive(): int
    {
        return RentalEloquent::where('status', RentalStatus::ACTIVE->value)->count();
    }

    public function countActiveByUser(int $userId): int
    {
        return RentalEloquent::where('user_id', $userId)
            ->where('status', RentalStatus::ACTIVE->value)
            ->count();
    }

    public function create(Rental $rental): Rental
    {
        $model = RentalEloquent::create([
            'user_id' => $rental->userId,
            'unit_id' => $rental->unitId,
            'rented_at' => $rental->rentedAt->format('Y-m-d'),
            'due_at' => $rental->dueAt->format('Y-m-d'),
            'returned_at' => $rental->returnedAt?->format('Y-m-d'),
            'fine_amount' => $rental->fineAmount,
            'status' => $rental->status->value,
        ]);

        $model->load(['user', 'unit']);

        return $this->toEntity($model);
    }

    public function update(Rental $rental): Rental
    {
        $model = RentalEloquent::findOrFail($rental->id);
        $model->update([
            'user_id' => $rental->userId,
            'unit_id' => $rental->unitId,
            'rented_at' => $rental->rentedAt->format('Y-m-d'),
            'due_at' => $rental->dueAt->format('Y-m-d'),
            'returned_at' => $rental->returnedAt?->format('Y-m-d'),
            'fine_amount' => $rental->fineAmount,
            'status' => $rental->status->value,
        ]);

        $model->load(['user', 'unit']);

        return $this->toEntity($model);
    }

    public function lockForUpdate(int $id): ?Rental
    {
        $model = RentalEloquent::with(['user', 'unit'])
            ->lockForUpdate()
            ->find($id);

        return $model ? $this->toEntity($model) : null;
    }

    private function toEntity(RentalEloquent $model): Rental
    {
        $user = null;
        if ($model->user) {
            $user = new User(
                id: $model->user->id,
                name: $model->user->name,
                email: new Email($model->user->email),
                password: $model->user->password,
                role: UserRole::from($model->user->role),
                profile: null,
                rememberToken: $model->user->remember_token,
                emailVerifiedAt: $model->user->email_verified_at?->toDateTimeImmutable(),
                createdAt: $model->user->created_at?->toDateTimeImmutable(),
                updatedAt: $model->user->updated_at?->toDateTimeImmutable(),
                deletedAt: $model->user->deleted_at?->toDateTimeImmutable(),
            );
        }

        $unit = null;
        if ($model->unit) {
            $unit = new Unit(
                id: $model->unit->id,
                code: $model->unit->code,
                name: $model->unit->name,
                description: $model->unit->description,
                status: UnitStatus::from($model->unit->status),
                createdAt: $model->unit->created_at?->toDateTimeImmutable(),
                updatedAt: $model->unit->updated_at?->toDateTimeImmutable(),
                deletedAt: $model->unit->deleted_at?->toDateTimeImmutable(),
                categories: [],
            );
        }

        return new Rental(
            id: $model->id,
            userId: $model->user_id,
            unitId: $model->unit_id,
            rentedAt: $model->rented_at->toDateTimeImmutable(),
            dueAt: $model->due_at->toDateTimeImmutable(),
            returnedAt: $model->returned_at?->toDateTimeImmutable(),
            fineAmount: $model->fine_amount,
            status: RentalStatus::from($model->status),
            createdAt: $model->created_at?->toDateTimeImmutable(),
            updatedAt: $model->updated_at?->toDateTimeImmutable(),
            user: $user,
            unit: $unit,
        );
    }
}
