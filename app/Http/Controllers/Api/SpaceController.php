<?php

namespace App\Http\Controllers\Api;

use App\Services\Contracts\SpaceServiceInterface;
use App\Http\Requests\Space\{
    CreateSpaceRequest,
    UpdateSpaceRequest
};
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Exceptions\ValidationException;
use Exception;

class SpaceController extends BaseApiController
{
    protected $spaceService;

    public function __construct(SpaceServiceInterface $spaceService)
    {
        $this->spaceService = $spaceService;
    }

    public function index(): JsonResponse
    {
        try {
            $spaces = $this->spaceService->all();
            return $this->successResponse($spaces);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function store(CreateSpaceRequest $request): JsonResponse
    {
        try {
            $space = $this->spaceService->create($request->validated());
            return $this->createdResponse($space);
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $space = $this->spaceService->find($id);
            return $this->successResponse($space);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 404);
        }
    }

    public function update(UpdateSpaceRequest $request, int $id): JsonResponse
    {
        try {
            $space = $this->spaceService->update($id, $request->validated());
            return $this->successResponse($space);
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->spaceService->delete($id);
            return $this->noContentResponse();
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function findByCode(string $code): JsonResponse
    {
        try {
            $space = $this->spaceService->findByCode($code);
            return $this->successResponse($space);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 404);
        }
    }

    public function getActiveSpaces(): JsonResponse
    {
        try {
            $spaces = $this->spaceService->getActiveSpaces();
            return $this->successResponse($spaces);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function getSpacesByFloor(int $floorId): JsonResponse
    {
        try {
            $spaces = $this->spaceService->getSpacesByFloor($floorId);
            return $this->successResponse($spaces);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function getSpacesByBuilding(int $buildingId): JsonResponse
    {
        try {
            $spaces = $this->spaceService->getSpacesByBuilding($buildingId);
            return $this->successResponse($spaces);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        try {
            $this->validate($request, [
                'status' => 'required|string|in:active,inactive,maintenance,occupied,vacant'
            ]);

            $space = $this->spaceService->updateStatus($id, $request->status);
            return $this->successResponse($space);
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function getSpaceWithAssets(int $id): JsonResponse
    {
        try {
            $space = $this->spaceService->getSpaceWithAssets($id);
            return $this->successResponse($space);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 404);
        }
    }

    public function getSpaceWithWorkOrders(int $id): JsonResponse
    {
        try {
            $space = $this->spaceService->getSpaceWithWorkOrders($id);
            return $this->successResponse($space);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 404);
        }
    }

    public function getStatistics(int $id): JsonResponse
    {
        try {
            $statistics = [
                'asset_count' => $this->spaceService->getAssetCount($id),
                'work_order_count' => $this->spaceService->getWorkOrderCount($id),
                'utilization_rate' => $this->spaceService->getSpaceUtilization($id),
                'maintenance_stats' => $this->spaceService->getMaintenanceStatistics($id)
            ];
            return $this->successResponse($statistics);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
} 