<?php

namespace App\Providers;

use App\Repositories\{
    BuildingRepository,
    FloorRepository,
    SpaceRepository,
    AssetCategoryRepository,
    AssetRepository,
    MaintenanceScheduleRepository,
    MaintenanceLogRepository,
    WorkOrderRepository,
    WorkOrderCommentRepository,
    WorkOrderAttachmentRepository
};
use App\Repositories\Contracts\{
    BuildingRepositoryInterface,
    FloorRepositoryInterface,
    SpaceRepositoryInterface,
    AssetCategoryRepositoryInterface,
    AssetRepositoryInterface,
    MaintenanceScheduleRepositoryInterface,
    MaintenanceLogRepositoryInterface,
    WorkOrderRepositoryInterface,
    WorkOrderCommentRepositoryInterface,
    WorkOrderAttachmentRepositoryInterface
};
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(BuildingRepositoryInterface::class, BuildingRepository::class);
        $this->app->bind(FloorRepositoryInterface::class, FloorRepository::class);
        $this->app->bind(SpaceRepositoryInterface::class, SpaceRepository::class);
        $this->app->bind(AssetCategoryRepositoryInterface::class, AssetCategoryRepository::class);
        $this->app->bind(AssetRepositoryInterface::class, AssetRepository::class);
        $this->app->bind(MaintenanceScheduleRepositoryInterface::class, MaintenanceScheduleRepository::class);
        $this->app->bind(MaintenanceLogRepositoryInterface::class, MaintenanceLogRepository::class);
        $this->app->bind(WorkOrderRepositoryInterface::class, WorkOrderRepository::class);
        $this->app->bind(WorkOrderCommentRepositoryInterface::class, WorkOrderCommentRepository::class);
        $this->app->bind(WorkOrderAttachmentRepositoryInterface::class, WorkOrderAttachmentRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
} 