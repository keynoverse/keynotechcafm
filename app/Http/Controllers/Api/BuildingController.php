<?php

namespace App\Http\Controllers\Api;

use App\Services\Contracts\BuildingServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Exceptions\ValidationException;
use Exception;

class BuildingController extends BaseApiController
{
    protected $buildingService;

    public function __construct(BuildingServiceInterface $buildingService)
    {
        $this->buildingService = $buildingService;
    }

    public function index(): JsonResponse
    {
        try {
            $buildings = $this->buildingService->all();
            return $this->successResponse($buildings);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $building = $this->buildingService->create($request->all());
            return $this->createdResponse($building);
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $building = $this->buildingService->find($id);
            return $this->successResponse($building);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 404);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $building = $this->buildingService->update($id, $request->all());
            return $this->successResponse($building);
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->buildingService->delete($id);
            return $this->noContentResponse();
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function findByCode(string $code): JsonResponse
    {
        try {
            $building = $this->buildingService->findByCode($code);
            return $this->successResponse($building);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 404);
        }
    }

    public function getActiveBuildings(): JsonResponse
    {
        try {
            $buildings = $this->buildingService->getActiveBuildings();
            return $this->successResponse($buildings);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function getBuildingWithSpaces(int $id): JsonResponse
    {
        try {
            $building = $this->buildingService->getBuildingWithSpaces($id);
            return $this->successResponse($building);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 404);
        }
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        try {
            $building = $this->buildingService->updateStatus($id, $request->status);
            return $this->successResponse($building);
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
                'space_count' => $this->buildingService->getSpaceCount($id),
                'asset_count' => $this->buildingService->getAssetCount($id),
                'occupancy_rate' => $this->buildingService->getOccupancyRate($id),
                'maintenance_stats' => $this->buildingService->getMaintenanceStatistics($id)
            ];
            return $this->successResponse($statistics);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
} 