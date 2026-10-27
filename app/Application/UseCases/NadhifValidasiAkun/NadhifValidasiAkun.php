<?php

namespace App\Application\UseCases\NadhifValidasiAkun;

use App\Infrastructure\Eloquent\UserEloquent;
use Illuminate\Support\Facades\DB;

class NadhifValidasiAkun
{
    public function NadhifValidasiAkun(int $id): bool
    {
        return DB::transaction(function () use ($id): bool {
            $user = UserEloquent::query()
                ->where('role', 'anggota')
                ->where('is_approved', false)
                ->lockForUpdate()
                ->find($id);

            if (! $user) {
                return false;
            }

            $user->is_approved = true;

            return $user->save();
        });
    }
}
