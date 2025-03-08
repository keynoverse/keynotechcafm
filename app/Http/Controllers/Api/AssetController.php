<?php

namespace App\Http\Controllers\Api;

use App\Services\Contracts\AssetServiceInterface;
use App\Http\Requests\Asset\{
    CreateAssetRequest,
    UpdateAssetRequest
};
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Exceptions\ValidationException;
use Exception;

class AssetController extends BaseApiController
{
    protected $assetService;

    public function __construct(AssetServiceInterface $assetService)
    {
        $this->assetService = $assetService;
    }

    public function index(): JsonResponse
    {
        try {
            $assets = $this->assetService->all();
            return $this->successResponse($assets);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function store(CreateAssetRequest $request): JsonResponse
    {
        try {
            $asset = $this->assetService->create($request->validated());
            return $this->createdResponse($asset);
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $asset = $this->assetService->find($id);
            return $this->successResponse($asset);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 404);
        }
    }

    public function update(UpdateAssetRequest $request, int $id): JsonResponse
    {
        try {
            $asset = $this->assetService->update($id, $request->validated());
            return $this->successResponse($asset);
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->assetService->delete($id);
            return $this->noContentResponse();
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function findByCode(string $code): JsonResponse
    {
        try {
            $asset = $this->assetService->findByCode($code);
            return $this->successResponse($asset);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 404);
        }
    }

    public function getActiveAssets(): JsonResponse
    {
        try {
            $assets = $this->assetService->getActiveAssets();
            return $this->successResponse($assets);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function getAssetsBySpace(int $spaceId): JsonResponse
    {
        try {
            $assets = $this->assetService->getAssetsBySpace($spaceId);
            return $this->successResponse($assets);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function getAssetsByFloor(int $floorId): JsonResponse
    {
        try {
            $assets = $this->assetService->getAssetsByFloor($floorId);
            return $this->successResponse($assets);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function getAssetsByBuilding(int $buildingId): JsonResponse
    {
        try {
            $assets = $this->assetService->getAssetsByBuilding($buildingId);
            return $this->successResponse($assets);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function getAssetsByCategory(int $categoryId): JsonResponse
    {
        try {
            $assets = $this->assetService->getAssetsByCategory($categoryId);
            return $this->successResponse($assets);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        try {
            $this->validate($request, [
                'status' => 'required|string|in:active,inactive,maintenance,retired,storage'
            ]);

            $asset = $this->assetService->updateStatus($id, $request->status);
            return $this->successResponse($asset);
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function assignToSpace(Request $request, int $id): JsonResponse
    {
        try {
            $this->validate($request, [
                'space_id' => 'required|exists:spaces,id'
            ]);

            $asset = $this->assetService->assignToSpace($id, $request->space_id);
            return $this->successResponse($asset);
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function getMaintenanceHistory(int $id): JsonResponse
    {
        try {
            $history = $this->assetService->getMaintenanceHistory($id);
            return $this->successResponse($history);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function getWorkOrderHistory(int $id): JsonResponse
    {
        try {
            $history = $this->assetService->getWorkOrderHistory($id);
            return $this->successResponse($history);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function getAssetWithMaintenanceSchedule(int $id): JsonResponse
    {
        try {
            $asset = $this->assetService->getAssetWithMaintenanceSchedule($id);
            return $this->successResponse($asset);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function getStatistics(int $id): JsonResponse
    {
        try {
            $statistics = $this->assetService->getAssetStatistics($id);
            return $this->successResponse($statistics);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function scheduleNextMaintenance(int $id): JsonResponse
    {
        try {
            $asset = $this->assetService->scheduleNextMaintenance($id);
            return $this->successResponse($asset);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
} 