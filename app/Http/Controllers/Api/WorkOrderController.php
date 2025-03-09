<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\WorkOrder\CreateWorkOrderRequest;
use App\Http\Requests\WorkOrder\UpdateWorkOrderRequest;
use App\Services\Contracts\WorkOrderServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Work Orders",
 *     description="API Endpoints for managing work orders"
 * )
 */
class WorkOrderController extends BaseApiController
{
    protected $service;

    public function __construct(WorkOrderServiceInterface $service)
    {
        $this->service = $service;
    }

    /**
     * @OA\Get(
     *     path="/api/work-orders",
     *     summary="Get all work orders",
     *     tags={"Work Orders"},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of work orders",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Work orders retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/WorkOrder"))
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 15);
        $workOrders = $this->service->getPaginatedWorkOrders($perPage);
        return $this->successResponse($workOrders, 'Work orders retrieved successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/work-orders",
     *     summary="Create a new work order",
     *     tags={"Work Orders"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/WorkOrder")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Work order created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Work order created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/WorkOrder")
     *         )
     *     )
     * )
     */
    public function store(CreateWorkOrderRequest $request): JsonResponse
    {
        $workOrder = $this->service->createWorkOrder($request->validated());
        return $this->successResponse($workOrder, 'Work order created successfully', 201);
    }

    /**
     * @OA\Get(
     *     path="/api/work-orders/{id}",
     *     summary="Get a work order by ID",
     *     tags={"Work Orders"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Work order ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Work order details",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Work order retrieved successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/WorkOrder")
     *         )
     *     )
     * )
     */
    public function show(int $id): JsonResponse
    {
        $workOrder = $this->service->getWorkOrderById($id);
        return $this->successResponse($workOrder, 'Work order retrieved successfully');
    }

    /**
     * @OA\Put(
     *     path="/api/work-orders/{id}",
     *     summary="Update a work order",
     *     tags={"Work Orders"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Work order ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/WorkOrder")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Work order updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Work order updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/WorkOrder")
     *         )
     *     )
     * )
     */
    public function update(UpdateWorkOrderRequest $request, int $id): JsonResponse
    {
        $workOrder = $this->service->updateWorkOrder($id, $request->validated());
        return $this->successResponse($workOrder, 'Work order updated successfully');
    }

    /**
     * @OA\Delete(
     *     path="/api/work-orders/{id}",
     *     summary="Delete a work order",
     *     tags={"Work Orders"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Work order ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Work order deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Work order deleted successfully")
     *         )
     *     )
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        $this->service->deleteWorkOrder($id);
        return $this->successResponse(null, 'Work order deleted successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/work-orders/asset/{assetId}",
     *     summary="Get work orders for an asset",
     *     tags={"Work Orders"},
     *     @OA\Parameter(
     *         name="assetId",
     *         in="path",
     *         description="Asset ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of work orders for the asset",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Asset work orders retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/WorkOrder"))
     *         )
     *     )
     * )
     */
    public function getAssetWorkOrders(int $assetId): JsonResponse
    {
        $workOrders = $this->service->getAssetWorkOrders($assetId);
        return $this->successResponse($workOrders, 'Asset work orders retrieved successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/work-orders/space/{spaceId}",
     *     summary="Get work orders for a space",
     *     tags={"Work Orders"},
     *     @OA\Parameter(
     *         name="spaceId",
     *         in="path",
     *         description="Space ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of work orders for the space",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Space work orders retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/WorkOrder"))
     *         )
     *     )
     * )
     */
    public function getSpaceWorkOrders(int $spaceId): JsonResponse
    {
        $workOrders = $this->service->getSpaceWorkOrders($spaceId);
        return $this->successResponse($workOrders, 'Space work orders retrieved successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/work-orders/date-range",
     *     summary="Get work orders within a date range",
     *     tags={"Work Orders"},
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         description="Start date",
     *         required=true,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         description="End date",
     *         required=true,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of work orders within the date range",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Work orders retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/WorkOrder"))
     *         )
     *     )
     * )
     */
    public function getByDateRange(Request $request): JsonResponse
    {
        $workOrders = $this->service->getWorkOrdersByDateRange(
            $request->input('start_date'),
            $request->input('end_date')
        );
        return $this->successResponse($workOrders, 'Work orders retrieved successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/work-orders/type/{type}",
     *     summary="Get work orders by type",
     *     tags={"Work Orders"},
     *     @OA\Parameter(
     *         name="type",
     *         in="path",
     *         description="Work order type",
     *         required=true,
     *         @OA\Schema(type="string", enum={"corrective", "preventive", "emergency", "inspection"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of work orders of the specified type",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Work orders retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/WorkOrder"))
     *         )
     *     )
     * )
     */
    public function getByType(string $type): JsonResponse
    {
        $workOrders = $this->service->getWorkOrdersByType($type);
        return $this->successResponse($workOrders, 'Work orders retrieved successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/work-orders/priority/{priority}",
     *     summary="Get work orders by priority",
     *     tags={"Work Orders"},
     *     @OA\Parameter(
     *         name="priority",
     *         in="path",
     *         description="Work order priority",
     *         required=true,
     *         @OA\Schema(type="string", enum={"low", "medium", "high", "critical"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of work orders with the specified priority",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Work orders retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/WorkOrder"))
     *         )
     *     )
     * )
     */
    public function getByPriority(string $priority): JsonResponse
    {
        $workOrders = $this->service->getWorkOrdersByPriority($priority);
        return $this->successResponse($workOrders, 'Work orders retrieved successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/work-orders/status/{status}",
     *     summary="Get work orders by status",
     *     tags={"Work Orders"},
     *     @OA\Parameter(
     *         name="status",
     *         in="path",
     *         description="Work order status",
     *         required=true,
     *         @OA\Schema(type="string", enum={"pending", "assigned", "in_progress", "on_hold", "completed", "cancelled"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of work orders with the specified status",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Work orders retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/WorkOrder"))
     *         )
     *     )
     * )
     */
    public function getByStatus(string $status): JsonResponse
    {
        $workOrders = $this->service->getWorkOrdersByStatus($status);
        return $this->successResponse($workOrders, 'Work orders retrieved successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/work-orders/assignee/{assigneeId}",
     *     summary="Get work orders by assignee",
     *     tags={"Work Orders"},
     *     @OA\Parameter(
     *         name="assigneeId",
     *         in="path",
     *         description="Assignee ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of work orders assigned to the user",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Work orders retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/WorkOrder"))
     *         )
     *     )
     * )
     */
    public function getByAssignee(int $assigneeId): JsonResponse
    {
        $workOrders = $this->service->getWorkOrdersByAssignee($assigneeId);
        return $this->successResponse($workOrders, 'Work orders retrieved successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/work-orders/requestor/{requestorId}",
     *     summary="Get work orders by requestor",
     *     tags={"Work Orders"},
     *     @OA\Parameter(
     *         name="requestorId",
     *         in="path",
     *         description="Requestor ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of work orders requested by the user",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Work orders retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/WorkOrder"))
     *         )
     *     )
     * )
     */
    public function getByRequestor(int $requestorId): JsonResponse
    {
        $workOrders = $this->service->getWorkOrdersByRequestor($requestorId);
        return $this->successResponse($workOrders, 'Work orders retrieved successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/work-orders/{id}/comments",
     *     summary="Add a comment to a work order",
     *     tags={"Work Orders"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Work order ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_id", "comment"},
     *             @OA\Property(property="user_id", type="integer"),
     *             @OA\Property(property="comment", type="string"),
     *             @OA\Property(property="metadata", type="object", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Comment added successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Comment added successfully")
     *         )
     *     )
     * )
     */
    public function addComment(Request $request, int $id): JsonResponse
    {
        $this->service->addComment($id, $request->all());
        return $this->successResponse(null, 'Comment added successfully');
    }

    /**
     * @OA\Delete(
     *     path="/api/work-orders/{id}/comments/{commentId}",
     *     summary="Remove a comment from a work order",
     *     tags={"Work Orders"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Work order ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="commentId",
     *         in="path",
     *         description="Comment ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Comment removed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Comment removed successfully")
     *         )
     *     )
     * )
     */
    public function removeComment(int $id, int $commentId): JsonResponse
    {
        $this->service->removeComment($id, $commentId);
        return $this->successResponse(null, 'Comment removed successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/work-orders/{id}/attachments",
     *     summary="Add an attachment to a work order",
     *     tags={"Work Orders"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Work order ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"file_name", "file_path", "file_type", "file_size"},
     *             @OA\Property(property="file_name", type="string"),
     *             @OA\Property(property="file_path", type="string"),
     *             @OA\Property(property="file_type", type="string"),
     *             @OA\Property(property="file_size", type="integer"),
     *             @OA\Property(property="description", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Attachment added successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Attachment added successfully")
     *         )
     *     )
     * )
     */
    public function addAttachment(Request $request, int $id): JsonResponse
    {
        $this->service->addAttachment($id, $request->all());
        return $this->successResponse(null, 'Attachment added successfully');
    }

    /**
     * @OA\Delete(
     *     path="/api/work-orders/{id}/attachments/{attachmentId}",
     *     summary="Remove an attachment from a work order",
     *     tags={"Work Orders"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Work order ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="attachmentId",
     *         in="path",
     *         description="Attachment ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Attachment removed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Attachment removed successfully")
     *         )
     *     )
     * )
     */
    public function removeAttachment(int $id, int $attachmentId): JsonResponse
    {
        $this->service->removeAttachment($id, $attachmentId);
        return $this->successResponse(null, 'Attachment removed successfully');
    }

    /**
     * @OA\Patch(
     *     path="/api/work-orders/{id}/status",
     *     summary="Update work order status",
     *     tags={"Work Orders"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Work order ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"status"},
     *             @OA\Property(property="status", type="string", enum={"pending", "assigned", "in_progress", "on_hold", "completed", "cancelled"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Work order status updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Work order status updated successfully")
     *         )
     *     )
     * )
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $this->service->updateWorkOrderStatus($id, $request->input('status'));
        return $this->successResponse(null, 'Work order status updated successfully');
    }

    /**
     * @OA\Patch(
     *     path="/api/work-orders/{id}/assign",
     *     summary="Assign a work order to a user",
     *     tags={"Work Orders"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Work order ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"assignee_id"},
     *             @OA\Property(property="assignee_id", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Work order assigned successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Work order assigned successfully")
     *         )
     *     )
     * )
     */
    public function assign(Request $request, int $id): JsonResponse
    {
        $this->service->assignWorkOrder($id, $request->input('assignee_id'));
        return $this->successResponse(null, 'Work order assigned successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/work-orders/statistics",
     *     summary="Get work order statistics",
     *     tags={"Work Orders"},
     *     @OA\Response(
     *         response=200,
     *         description="Work order statistics",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Work order statistics retrieved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="total", type="integer", example=100),
     *                 @OA\Property(property="pending", type="integer", example=20),
     *                 @OA\Property(property="in_progress", type="integer", example=30),
     *                 @OA\Property(property="completed", type="integer", example=40),
     *                 @OA\Property(property="overdue", type="integer", example=10),
     *                 @OA\Property(property="by_priority", type="object",
     *                     @OA\Property(property="low", type="integer", example=25),
     *                     @OA\Property(property="medium", type="integer", example=35),
     *                     @OA\Property(property="high", type="integer", example=30),
     *                     @OA\Property(property="critical", type="integer", example=10)
     *                 ),
     *                 @OA\Property(property="by_type", type="object",
     *                     @OA\Property(property="corrective", type="integer", example=40),
     *                     @OA\Property(property="preventive", type="integer", example=30),
     *                     @OA\Property(property="emergency", type="integer", example=20),
     *                     @OA\Property(property="inspection", type="integer", example=10)
     *                 ),
     *                 @OA\Property(property="completion_rate", type="number", format="float", example=40.0)
     *             )
     *         )
     *     )
     * )
     */
    public function statistics(): JsonResponse
    {
        $statistics = $this->service->getWorkOrderStatistics();
        return $this->successResponse($statistics, 'Work order statistics retrieved successfully');
    }
} 