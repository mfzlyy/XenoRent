<?php

namespace App\Application\Interfaces;

use App\Domain\Entities\Profile;
use App\Domain\Entities\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface UserRepositoryInterface
{
    public function findById(int $id): ?User;

    public function lockForUpdate(int $id): ?User;

    public function findByEmail(string $email): ?User;

    public function create(User $user, Profile $profile): User;

    public function update(User $user, Profile $profile): User;

    public function delete(int $id): bool;

    public function getMembers(int $perPage = 15, string $search = ''): LengthAwarePaginator;

    public function countMembers(): int;
}
