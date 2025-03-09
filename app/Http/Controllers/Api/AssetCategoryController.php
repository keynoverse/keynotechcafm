<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssetCategory\CreateAssetCategoryRequest;
use App\Http\Requests\AssetCategory\UpdateAssetCategoryRequest;
use App\Services\Contracts\AssetCategoryServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Asset Categories",
 *     description="API Endpoints for managing asset categories"
 * )
 */
class AssetCategoryController extends BaseApiController
{
    protected $service;

    public function __construct(AssetCategoryServiceInterface $service)
    {
        $this->service = $service;
    }

    /**
     * @OA\Get(
     *     path="/api/asset-categories",
     *     summary="Get all asset categories",
     *     tags={"Asset Categories"},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number for pagination",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of asset categories",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Asset categories retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/AssetCategory"))
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 15);
        $categories = $this->service->getPaginatedCategories($perPage);
        return $this->successResponse($categories, 'Asset categories retrieved successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/asset-categories",
     *     summary="Create a new asset category",
     *     tags={"Asset Categories"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "status"},
     *             @OA\Property(property="name", type="string", example="Electronics"),
     *             @OA\Property(property="description", type="string", example="Electronic equipment and devices"),
     *             @OA\Property(property="parent_id", type="integer", nullable=true, example=1),
     *             @OA\Property(property="status", type="string", enum={"active", "inactive"}, example="active"),
     *             @OA\Property(property="metadata", type="object", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Asset category created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Asset category created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/AssetCategory")
     *         )
     *     )
     * )
     */
    public function store(CreateAssetCategoryRequest $request): JsonResponse
    {
        $category = $this->service->createCategory($request->validated());
        return $this->successResponse($category, 'Asset category created successfully', 201);
    }

    /**
     * @OA\Get(
     *     path="/api/asset-categories/{id}",
     *     summary="Get an asset category by ID",
     *     tags={"Asset Categories"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Asset category ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Asset category details",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Asset category retrieved successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/AssetCategory")
     *         )
     *     )
     * )
     */
    public function show(int $id): JsonResponse
    {
        $category = $this->service->getCategoryById($id);
        return $this->successResponse($category, 'Asset category retrieved successfully');
    }

    /**
     * @OA\Put(
     *     path="/api/asset-categories/{id}",
     *     summary="Update an asset category",
     *     tags={"Asset Categories"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Asset category ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Updated Electronics"),
     *             @OA\Property(property="description", type="string", example="Updated description"),
     *             @OA\Property(property="parent_id", type="integer", nullable=true, example=1),
     *             @OA\Property(property="status", type="string", enum={"active", "inactive"}, example="active"),
     *             @OA\Property(property="metadata", type="object", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Asset category updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Asset category updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/AssetCategory")
     *         )
     *     )
     * )
     */
    public function update(UpdateAssetCategoryRequest $request, int $id): JsonResponse
    {
        $category = $this->service->updateCategory($id, $request->validated());
        return $this->successResponse($category, 'Asset category updated successfully');
    }

    /**
     * @OA\Delete(
     *     path="/api/asset-categories/{id}",
     *     summary="Delete an asset category",
     *     tags={"Asset Categories"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Asset category ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Asset category deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Asset category deleted successfully")
     *         )
     *     )
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        $this->service->deleteCategory($id);
        return $this->successResponse(null, 'Asset category deleted successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/asset-categories/hierarchy",
     *     summary="Get asset category hierarchy",
     *     tags={"Asset Categories"},
     *     @OA\Response(
     *         response=200,
     *         description="Asset category hierarchy",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Asset category hierarchy retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/AssetCategory"))
     *         )
     *     )
     * )
     */
    public function hierarchy(): JsonResponse
    {
        $hierarchy = $this->service->getCategoryHierarchy();
        return $this->successResponse($hierarchy, 'Asset category hierarchy retrieved successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/asset-categories/active",
     *     summary="Get active asset categories",
     *     tags={"Asset Categories"},
     *     @OA\Response(
     *         response=200,
     *         description="List of active asset categories",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Active asset categories retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/AssetCategory"))
     *         )
     *     )
     * )
     */
    public function active(): JsonResponse
    {
        $categories = $this->service->getActiveCategories();
        return $this->successResponse($categories, 'Active asset categories retrieved successfully');
    }

    /**
     * @OA\Patch(
     *     path="/api/asset-categories/{id}/move",
     *     summary="Move an asset category to a new parent",
     *     tags={"Asset Categories"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Asset category ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="parent_id", type="integer", nullable=true, example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Asset category moved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Asset category moved successfully")
     *         )
     *     )
     * )
     */
    public function move(Request $request, int $id): JsonResponse
    {
        $parentId = $request->input('parent_id');
        $success = $this->service->moveCategory($id, $parentId);
        return $this->successResponse(null, 'Asset category moved successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/asset-categories/{id}/children",
     *     summary="Get child categories of a parent category",
     *     tags={"Asset Categories"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Parent category ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of child categories",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Child categories retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/AssetCategory"))
     *         )
     *     )
     * )
     */
    public function children(int $id): JsonResponse
    {
        $children = $this->service->getChildCategories($id);
        return $this->successResponse($children, 'Child categories retrieved successfully');
    }

    /**
     * @OA\Patch(
     *     path="/api/asset-categories/{id}/status",
     *     summary="Update asset category status",
     *     tags={"Asset Categories"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Asset category ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", enum={"active", "inactive"}, example="active")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Asset category status updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Asset category status updated successfully")
     *         )
     *     )
     * )
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $status = $request->input('status');
        $success = $this->service->updateCategoryStatus($id, $status);
        return $this->successResponse(null, 'Asset category status updated successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/asset-categories/search",
     *     summary="Search asset categories",
     *     tags={"Asset Categories"},
     *     @OA\Parameter(
     *         name="term",
     *         in="query",
     *         description="Search term",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Search results",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Asset categories found successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/AssetCategory"))
     *         )
     *     )
     * )
     */
    public function search(Request $request): JsonResponse
    {
        $term = $request->input('term');
        $results = $this->service->searchCategories($term);
        return $this->successResponse($results, 'Asset categories found successfully');
    }
} 