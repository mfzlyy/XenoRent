<?php

namespace App\Providers;

use App\Application\Interfaces\CategoryRepositoryInterface;
use App\Application\Interfaces\RentalRepositoryInterface;
use App\Application\Interfaces\RentalSettingsRepositoryInterface;
use App\Application\Interfaces\UnitRepositoryInterface;
use App\Application\Interfaces\UserRepositoryInterface;
use App\Infrastructure\Repositories\EloquentCategoryRepository;
use App\Infrastructure\Repositories\EloquentRentalRepository;
use App\Infrastructure\Repositories\EloquentRentalSettingsRepository;
use App\Infrastructure\Repositories\EloquentUnitRepository;
use App\Infrastructure\Repositories\EloquentUserRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);
        $this->app->bind(UnitRepositoryInterface::class, EloquentUnitRepository::class);
        $this->app->bind(CategoryRepositoryInterface::class, EloquentCategoryRepository::class);
        $this->app->bind(RentalRepositoryInterface::class, EloquentRentalRepository::class);
        $this->app->bind(RentalSettingsRepositoryInterface::class, EloquentRentalSettingsRepository::class);
    }

    public function boot(): void
    {
        //
    }
}
