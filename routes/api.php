<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\{
    BuildingController,
    FloorController,
    SpaceController,
    AssetController
};

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {
    // Building routes
    Route::prefix('buildings')->group(function () {
        Route::get('/', [BuildingController::class, 'index']);
        Route::post('/', [BuildingController::class, 'store']);
        Route::get('/active', [BuildingController::class, 'getActiveBuildings']);
        Route::get('/{id}', [BuildingController::class, 'show']);
        Route::put('/{id}', [BuildingController::class, 'update']);
        Route::delete('/{id}', [BuildingController::class, 'destroy']);
        Route::get('/code/{code}', [BuildingController::class, 'findByCode']);
        Route::get('/{id}/spaces', [BuildingController::class, 'getBuildingWithSpaces']);
        Route::patch('/{id}/status', [BuildingController::class, 'updateStatus']);
        Route::get('/{id}/statistics', [BuildingController::class, 'getStatistics']);
    });

    // Floor routes
    Route::prefix('floors')->group(function () {
        Route::get('/', [FloorController::class, 'index']);
        Route::post('/', [FloorController::class, 'store']);
        Route::get('/active', [FloorController::class, 'getActiveFloors']);
        Route::get('/{id}', [FloorController::class, 'show']);
        Route::put('/{id}', [FloorController::class, 'update']);
        Route::delete('/{id}', [FloorController::class, 'destroy']);
        Route::get('/code/{code}', [FloorController::class, 'findByCode']);
        Route::get('/building/{buildingId}', [FloorController::class, 'getFloorsByBuilding']);
        Route::get('/{id}/spaces', [FloorController::class, 'getFloorWithSpaces']);
        Route::patch('/{id}/status', [FloorController::class, 'updateStatus']);
        Route::get('/{id}/statistics', [FloorController::class, 'getStatistics']);
    });

    // Space routes
    Route::prefix('spaces')->group(function () {
        Route::get('/', [SpaceController::class, 'index']);
        Route::post('/', [SpaceController::class, 'store']);
        Route::get('/active', [SpaceController::class, 'getActiveSpaces']);
        Route::get('/{id}', [SpaceController::class, 'show']);
        Route::put('/{id}', [SpaceController::class, 'update']);
        Route::delete('/{id}', [SpaceController::class, 'destroy']);
        Route::get('/code/{code}', [SpaceController::class, 'findByCode']);
        Route::get('/floor/{floorId}', [SpaceController::class, 'getSpacesByFloor']);
        Route::get('/building/{buildingId}', [SpaceController::class, 'getSpacesByBuilding']);
        Route::patch('/{id}/status', [SpaceController::class, 'updateStatus']);
        Route::get('/{id}/assets', [SpaceController::class, 'getSpaceWithAssets']);
        Route::get('/{id}/work-orders', [SpaceController::class, 'getSpaceWithWorkOrders']);
        Route::get('/{id}/statistics', [SpaceController::class, 'getStatistics']);
    });

    // Asset routes
    Route::prefix('assets')->group(function () {
        Route::get('/', [AssetController::class, 'index']);
        Route::post('/', [AssetController::class, 'store']);
        Route::get('/active', [AssetController::class, 'getActiveAssets']);
        Route::get('/{id}', [AssetController::class, 'show']);
        Route::put('/{id}', [AssetController::class, 'update']);
        Route::delete('/{id}', [AssetController::class, 'destroy']);
        Route::get('/code/{code}', [AssetController::class, 'findByCode']);
        Route::get('/space/{spaceId}', [AssetController::class, 'getAssetsBySpace']);
        Route::get('/floor/{floorId}', [AssetController::class, 'getAssetsByFloor']);
        Route::get('/building/{buildingId}', [AssetController::class, 'getAssetsByBuilding']);
        Route::get('/category/{categoryId}', [AssetController::class, 'getAssetsByCategory']);
        Route::patch('/{id}/status', [AssetController::class, 'updateStatus']);
        Route::patch('/{id}/assign', [AssetController::class, 'assignToSpace']);
        Route::get('/{id}/maintenance-history', [AssetController::class, 'getMaintenanceHistory']);
        Route::get('/{id}/work-orders', [AssetController::class, 'getWorkOrderHistory']);
        Route::get('/{id}/maintenance-schedule', [AssetController::class, 'getAssetWithMaintenanceSchedule']);
        Route::get('/{id}/statistics', [AssetController::class, 'getStatistics']);
        Route::post('/{id}/schedule-maintenance', [AssetController::class, 'scheduleNextMaintenance']);
    });
}); 