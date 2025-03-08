<?php

namespace App\Providers;

use App\Services\{
    BuildingService,
    FloorService,
    SpaceService,
    AssetService
};
use App\Services\Contracts\{
    BuildingServiceInterface,
    FloorServiceInterface,
    SpaceServiceInterface,
    AssetServiceInterface
};
use Illuminate\Support\ServiceProvider;

class ServiceServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(BuildingServiceInterface::class, BuildingService::class);
        $this->app->bind(FloorServiceInterface::class, FloorService::class);
        $this->app->bind(SpaceServiceInterface::class, SpaceService::class);
        $this->app->bind(AssetServiceInterface::class, AssetService::class);
        
        // Add more service bindings here as we create them
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
} 