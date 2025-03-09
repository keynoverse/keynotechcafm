<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\{
    BuildingController,
    FloorController,
    SpaceController,
    AssetController,
    AssetCategoryController,
    MaintenanceScheduleController,
    MaintenanceLogController,
    WorkOrderController
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

    // Asset Categories
    Route::prefix('asset-categories')->group(function () {
        Route::get('/', [AssetCategoryController::class, 'index']);
        Route::post('/', [AssetCategoryController::class, 'store']);
        Route::get('/active', [AssetCategoryController::class, 'active']);
        Route::get('/hierarchy', [AssetCategoryController::class, 'hierarchy']);
        Route::get('/search', [AssetCategoryController::class, 'search']);
        Route::get('/{id}', [AssetCategoryController::class, 'show']);
        Route::put('/{id}', [AssetCategoryController::class, 'update']);
        Route::delete('/{id}', [AssetCategoryController::class, 'destroy']);
        Route::patch('/{id}/move', [AssetCategoryController::class, 'move']);
        Route::get('/{id}/children', [AssetCategoryController::class, 'children']);
        Route::patch('/{id}/status', [AssetCategoryController::class, 'updateStatus']);
    });

    // Maintenance Schedules
    Route::prefix('maintenance-schedules')->group(function () {
        Route::get('/', [MaintenanceScheduleController::class, 'index']);
        Route::post('/', [MaintenanceScheduleController::class, 'store']);
        Route::get('/upcoming', [MaintenanceScheduleController::class, 'upcoming']);
        Route::get('/overdue', [MaintenanceScheduleController::class, 'overdue']);
        Route::get('/date-range', [MaintenanceScheduleController::class, 'getByDateRange']);
        Route::get('/asset/{assetId}', [MaintenanceScheduleController::class, 'getAssetSchedules']);
        Route::get('/status/{status}', [MaintenanceScheduleController::class, 'getByStatus']);
        Route::get('/{id}', [MaintenanceScheduleController::class, 'show']);
        Route::put('/{id}', [MaintenanceScheduleController::class, 'update']);
        Route::delete('/{id}', [MaintenanceScheduleController::class, 'destroy']);
        Route::post('/{id}/complete', [MaintenanceScheduleController::class, 'complete']);
        Route::patch('/{id}/reschedule', [MaintenanceScheduleController::class, 'reschedule']);
        Route::patch('/{id}/status', [MaintenanceScheduleController::class, 'updateStatus']);
    });

    // Maintenance Logs
    Route::prefix('maintenance-logs')->group(function () {
        Route::get('/', [MaintenanceLogController::class, 'index']);
        Route::post('/', [MaintenanceLogController::class, 'store']);
        Route::get('/asset/{assetId}', [MaintenanceLogController::class, 'getAssetLogs']);
        Route::get('/schedule/{scheduleId}', [MaintenanceLogController::class, 'getScheduleLogs']);
        Route::get('/date-range', [MaintenanceLogController::class, 'getByDateRange']);
        Route::get('/type/{type}', [MaintenanceLogController::class, 'getByType']);
        Route::get('/status/{status}', [MaintenanceLogController::class, 'getByStatus']);
        Route::get('/technician/{technicianId}', [MaintenanceLogController::class, 'getByTechnician']);
        Route::get('/{id}', [MaintenanceLogController::class, 'show']);
        Route::put('/{id}', [MaintenanceLogController::class, 'update']);
        Route::delete('/{id}', [MaintenanceLogController::class, 'destroy']);
        Route::post('/{id}/attachments', [MaintenanceLogController::class, 'addAttachment']);
        Route::delete('/{id}/attachments/{attachmentId}', [MaintenanceLogController::class, 'removeAttachment']);
        Route::patch('/{id}/status', [MaintenanceLogController::class, 'updateStatus']);
    });

    // Work Orders
    Route::prefix('work-orders')->group(function () {
        Route::get('/', [WorkOrderController::class, 'index']);
        Route::post('/', [WorkOrderController::class, 'store']);
        Route::get('/asset/{assetId}', [WorkOrderController::class, 'getAssetWorkOrders']);
        Route::get('/space/{spaceId}', [WorkOrderController::class, 'getSpaceWorkOrders']);
        Route::get('/date-range', [WorkOrderController::class, 'getByDateRange']);
        Route::get('/type/{type}', [WorkOrderController::class, 'getByType']);
        Route::get('/priority/{priority}', [WorkOrderController::class, 'getByPriority']);
        Route::get('/status/{status}', [WorkOrderController::class, 'getByStatus']);
        Route::get('/assignee/{assigneeId}', [WorkOrderController::class, 'getByAssignee']);
        Route::get('/requestor/{requestorId}', [WorkOrderController::class, 'getByRequestor']);
        Route::get('/statistics', [WorkOrderController::class, 'statistics']);
        Route::get('/{id}', [WorkOrderController::class, 'show']);
        Route::put('/{id}', [WorkOrderController::class, 'update']);
        Route::delete('/{id}', [WorkOrderController::class, 'destroy']);
        Route::post('/{id}/comments', [WorkOrderController::class, 'addComment']);
        Route::delete('/{id}/comments/{commentId}', [WorkOrderController::class, 'removeComment']);
        Route::post('/{id}/attachments', [WorkOrderController::class, 'addAttachment']);
        Route::delete('/{id}/attachments/{attachmentId}', [WorkOrderController::class, 'removeAttachment']);
        Route::patch('/{id}/status', [WorkOrderController::class, 'updateStatus']);
        Route::patch('/{id}/assign', [WorkOrderController::class, 'assign']);
    });
}); 