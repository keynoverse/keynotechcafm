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

/**
 * @OA\Tag(
 *     name="Assets",
 *     description="API Endpoints for managing assets"
 * )
 */
class AssetController extends BaseApiController
{
    protected $assetService;

    public function __construct(AssetServiceInterface $assetService)
    {
        $this->assetService = $assetService;
    }

    /**
     * @OA\Get(
     *     path="/api/assets",
     *     summary="Get all assets",
     *     description="Retrieve a list of all assets",
     *     operationId="getAssets",
     *     tags={"Assets"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Asset")),
     *             @OA\Property(property="message", type="string", example="Assets retrieved successfully")
     *         )
     *     ),
     *     @OA\Response(response=401, ref="#/components/responses/401"),
     *     @OA\Response(response=403, ref="#/components/responses/403"),
     *     @OA\Response(response=500, ref="#/components/responses/500")
     * )
     */
    public function index(): JsonResponse
    {
        try {
            $assets = $this->assetService->all();
            return $this->successResponse($assets);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *     path="/api/assets",
     *     summary="Create a new asset",
     *     description="Create a new asset with the provided data",
     *     operationId="createAsset",
     *     tags={"Assets"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/CreateAssetRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Asset created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Asset"),
     *             @OA\Property(property="message", type="string", example="Asset created successfully")
     *         )
     *     ),
     *     @OA\Response(response=401, ref="#/components/responses/401"),
     *     @OA\Response(response=403, ref="#/components/responses/403"),
     *     @OA\Response(response=422, ref="#/components/responses/422"),
     *     @OA\Response(response=500, ref="#/components/responses/500")
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/api/assets/{id}",
     *     summary="Get asset by ID",
     *     description="Retrieve an asset by its ID",
     *     operationId="getAssetById",
     *     tags={"Assets"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the asset",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Asset"),
     *             @OA\Property(property="message", type="string", example="Asset retrieved successfully")
     *         )
     *     ),
     *     @OA\Response(response=401, ref="#/components/responses/401"),
     *     @OA\Response(response=403, ref="#/components/responses/403"),
     *     @OA\Response(response=404, ref="#/components/responses/404"),
     *     @OA\Response(response=500, ref="#/components/responses/500")
     * )
     */
    public function show(int $id): JsonResponse
    {
        try {
            $asset = $this->assetService->find($id);
            return $this->successResponse($asset);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 404);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/assets/{id}",
     *     summary="Update an asset",
     *     description="Update an existing asset with the provided data",
     *     operationId="updateAsset",
     *     tags={"Assets"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the asset to update",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UpdateAssetRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Asset updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Asset"),
     *             @OA\Property(property="message", type="string", example="Asset updated successfully")
     *         )
     *     ),
     *     @OA\Response(response=401, ref="#/components/responses/401"),
     *     @OA\Response(response=403, ref="#/components/responses/403"),
     *     @OA\Response(response=404, ref="#/components/responses/404"),
     *     @OA\Response(response=422, ref="#/components/responses/422"),
     *     @OA\Response(response=500, ref="#/components/responses/500")
     * )
     */
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

    /**
     * @OA\Delete(
     *     path="/api/assets/{id}",
     *     summary="Delete an asset",
     *     description="Delete an asset by its ID",
     *     operationId="deleteAsset",
     *     tags={"Assets"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the asset to delete",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Asset deleted successfully"
     *     ),
     *     @OA\Response(response=401, ref="#/components/responses/401"),
     *     @OA\Response(response=403, ref="#/components/responses/403"),
     *     @OA\Response(response=404, ref="#/components/responses/404"),
     *     @OA\Response(response=500, ref="#/components/responses/500")
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->assetService->delete($id);
            return $this->noContentResponse();
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *     path="/api/assets/code/{code}",
     *     summary="Find asset by code",
     *     description="Retrieve an asset by its unique code",
     *     operationId="findAssetByCode",
     *     tags={"Assets"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="code",
     *         in="path",
     *         description="Code of the asset",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Asset"),
     *             @OA\Property(property="message", type="string", example="Asset found successfully")
     *         )
     *     ),
     *     @OA\Response(response=401, ref="#/components/responses/401"),
     *     @OA\Response(response=403, ref="#/components/responses/403"),
     *     @OA\Response(response=404, ref="#/components/responses/404"),
     *     @OA\Response(response=500, ref="#/components/responses/500")
     * )
     */
    public function findByCode(string $code): JsonResponse
    {
        try {
            $asset = $this->assetService->findByCode($code);
            return $this->successResponse($asset);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 404);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/assets/active",
     *     summary="Get active assets",
     *     description="Retrieve a list of all active assets",
     *     operationId="getActiveAssets",
     *     tags={"Assets"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Asset")),
     *             @OA\Property(property="message", type="string", example="Active assets retrieved successfully")
     *         )
     *     ),
     *     @OA\Response(response=401, ref="#/components/responses/401"),
     *     @OA\Response(response=403, ref="#/components/responses/403"),
     *     @OA\Response(response=500, ref="#/components/responses/500")
     * )
     */
    public function getActiveAssets(): JsonResponse
    {
        try {
            $assets = $this->assetService->getActiveAssets();
            return $this->successResponse($assets);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *     path="/api/assets/space/{spaceId}",
     *     summary="Get assets by space",
     *     description="Retrieve all assets in a specific space",
     *     operationId="getAssetsBySpace",
     *     tags={"Assets"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="spaceId",
     *         in="path",
     *         description="ID of the space",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Asset")),
     *             @OA\Property(property="message", type="string", example="Assets retrieved successfully")
     *         )
     *     ),
     *     @OA\Response(response=401, ref="#/components/responses/401"),
     *     @OA\Response(response=403, ref="#/components/responses/403"),
     *     @OA\Response(response=404, ref="#/components/responses/404"),
     *     @OA\Response(response=500, ref="#/components/responses/500")
     * )
     */
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

    /**
     * @OA\Patch(
     *     path="/api/assets/{id}/status",
     *     summary="Update asset status",
     *     description="Update the status of an existing asset",
     *     operationId="updateAssetStatus",
     *     tags={"Assets"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the asset",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 enum={"active", "inactive", "maintenance", "retired", "storage"},
     *                 description="New status of the asset"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Asset status updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Asset"),
     *             @OA\Property(property="message", type="string", example="Asset status updated successfully")
     *         )
     *     ),
     *     @OA\Response(response=401, ref="#/components/responses/401"),
     *     @OA\Response(response=403, ref="#/components/responses/403"),
     *     @OA\Response(response=404, ref="#/components/responses/404"),
     *     @OA\Response(response=422, ref="#/components/responses/422"),
     *     @OA\Response(response=500, ref="#/components/responses/500")
     * )
     */
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

/**
 * @OA\Schema(
 *     schema="Asset",
 *     title="Asset",
 *     description="Asset model",
 *     @OA\Property(property="id", type="integer", format="int64", example=1),
 *     @OA\Property(property="category_id", type="integer", format="int64", example=1),
 *     @OA\Property(property="space_id", type="integer", format="int64", example=1),
 *     @OA\Property(property="name", type="string", example="Office Chair"),
 *     @OA\Property(property="code", type="string", example="AST-001"),
 *     @OA\Property(property="description", type="string", example="Ergonomic office chair"),
 *     @OA\Property(property="model", type="string", example="XYZ-123"),
 *     @OA\Property(property="manufacturer", type="string", example="ABC Corp"),
 *     @OA\Property(property="serial_number", type="string", example="SN123456"),
 *     @OA\Property(property="purchase_date", type="string", format="date", example="2023-01-01"),
 *     @OA\Property(property="purchase_cost", type="number", format="float", example=299.99),
 *     @OA\Property(property="warranty_expiry", type="string", format="date", example="2024-01-01"),
 *     @OA\Property(property="maintenance_frequency", type="integer", example=30),
 *     @OA\Property(property="maintenance_unit", type="string", example="days"),
 *     @OA\Property(property="next_maintenance_date", type="string", format="date", example="2023-12-31"),
 *     @OA\Property(property="status", type="string", enum={"active", "inactive", "maintenance", "retired", "storage"}),
 *     @OA\Property(property="condition", type="string", enum={"excellent", "good", "fair", "poor"}),
 *     @OA\Property(property="criticality", type="string", enum={"high", "medium", "low"}),
 *     @OA\Property(property="metadata", type="object"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */ 