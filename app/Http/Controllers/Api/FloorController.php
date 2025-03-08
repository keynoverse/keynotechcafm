<?php

namespace App\Http\Controllers\Api;

use App\Services\Contracts\FloorServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Exceptions\ValidationException;
use Exception;

class FloorController extends BaseApiController
{
    protected $floorService;

    public function __construct(FloorServiceInterface $floorService)
    {
        $this->floorService = $floorService;
    }

    public function index(): JsonResponse
    {
        try {
            $floors = $this->floorService->all();
            return $this->successResponse($floors);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $floor = $this->floorService->create($request->all());
            return $this->createdResponse($floor);
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $floor = $this->floorService->find($id);
            return $this->successResponse($floor);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 404);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $floor = $this->floorService->update($id, $request->all());
            return $this->successResponse($floor);
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->floorService->delete($id);
            return $this->noContentResponse();
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function findByCode(string $code): JsonResponse
    {
        try {
            $floor = $this->floorService->findByCode($code);
            return $this->successResponse($floor);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 404);
        }
    }

    public function getActiveFloors(): JsonResponse
    {
        try {
            $floors = $this->floorService->getActiveFloors();
            return $this->successResponse($floors);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function getFloorsByBuilding(int $buildingId): JsonResponse
    {
        try {
            $floors = $this->floorService->getFloorsByBuilding($buildingId);
            return $this->successResponse($floors);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function getFloorWithSpaces(int $id): JsonResponse
    {
        try {
            $floor = $this->floorService->getFloorWithSpaces($id);
            return $this->successResponse($floor);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 404);
        }
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        try {
            $floor = $this->floorService->updateStatus($id, $request->status);
            return $this->successResponse($floor);
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function getStatistics(int $id): JsonResponse
    {
        try {
            $statistics = [
                'space_count' => $this->floorService->getSpaceCount($id),
                'asset_count' => $this->floorService->getAssetCount($id),
                'occupancy_rate' => $this->floorService->getOccupancyRate($id),
                'maintenance_stats' => $this->floorService->getMaintenanceStatistics($id)
            ];
            return $this->successResponse($statistics);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
} 