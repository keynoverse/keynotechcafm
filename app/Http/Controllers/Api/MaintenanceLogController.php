<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\MaintenanceLog\CreateMaintenanceLogRequest;
use App\Http\Requests\MaintenanceLog\UpdateMaintenanceLogRequest;
use App\Services\Contracts\MaintenanceLogServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Maintenance Logs",
 *     description="API Endpoints for managing maintenance logs"
 * )
 */
class MaintenanceLogController extends BaseApiController
{
    protected $service;

    public function __construct(MaintenanceLogServiceInterface $service)
    {
        $this->service = $service;
    }

    /**
     * @OA\Get(
     *     path="/api/maintenance-logs",
     *     summary="Get all maintenance logs",
     *     tags={"Maintenance Logs"},
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
     *         description="List of maintenance logs",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Maintenance logs retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/MaintenanceLog"))
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 15);
        $logs = $this->service->getPaginatedLogs($perPage);
        return $this->successResponse($logs, 'Maintenance logs retrieved successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/maintenance-logs",
     *     summary="Create a new maintenance log",
     *     tags={"Maintenance Logs"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/MaintenanceLog")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Maintenance log created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Maintenance log created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/MaintenanceLog")
     *         )
     *     )
     * )
     */
    public function store(CreateMaintenanceLogRequest $request): JsonResponse
    {
        $log = $this->service->createLog($request->validated());
        return $this->successResponse($log, 'Maintenance log created successfully', 201);
    }

    /**
     * @OA\Get(
     *     path="/api/maintenance-logs/{id}",
     *     summary="Get a maintenance log by ID",
     *     tags={"Maintenance Logs"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Maintenance log ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Maintenance log details",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Maintenance log retrieved successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/MaintenanceLog")
     *         )
     *     )
     * )
     */
    public function show(int $id): JsonResponse
    {
        $log = $this->service->getLogById($id);
        return $this->successResponse($log, 'Maintenance log retrieved successfully');
    }

    /**
     * @OA\Put(
     *     path="/api/maintenance-logs/{id}",
     *     summary="Update a maintenance log",
     *     tags={"Maintenance Logs"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Maintenance log ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/MaintenanceLog")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Maintenance log updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Maintenance log updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/MaintenanceLog")
     *         )
     *     )
     * )
     */
    public function update(UpdateMaintenanceLogRequest $request, int $id): JsonResponse
    {
        $log = $this->service->updateLog($id, $request->validated());
        return $this->successResponse($log, 'Maintenance log updated successfully');
    }

    /**
     * @OA\Delete(
     *     path="/api/maintenance-logs/{id}",
     *     summary="Delete a maintenance log",
     *     tags={"Maintenance Logs"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Maintenance log ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Maintenance log deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Maintenance log deleted successfully")
     *         )
     *     )
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        $this->service->deleteLog($id);
        return $this->successResponse(null, 'Maintenance log deleted successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/maintenance-logs/asset/{assetId}",
     *     summary="Get maintenance logs for an asset",
     *     tags={"Maintenance Logs"},
     *     @OA\Parameter(
     *         name="assetId",
     *         in="path",
     *         description="Asset ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of maintenance logs for the asset",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Asset maintenance logs retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/MaintenanceLog"))
     *         )
     *     )
     * )
     */
    public function getAssetLogs(int $assetId): JsonResponse
    {
        $logs = $this->service->getAssetLogs($assetId);
        return $this->successResponse($logs, 'Asset maintenance logs retrieved successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/maintenance-logs/schedule/{scheduleId}",
     *     summary="Get maintenance logs for a schedule",
     *     tags={"Maintenance Logs"},
     *     @OA\Parameter(
     *         name="scheduleId",
     *         in="path",
     *         description="Schedule ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of maintenance logs for the schedule",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Schedule maintenance logs retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/MaintenanceLog"))
     *         )
     *     )
     * )
     */
    public function getScheduleLogs(int $scheduleId): JsonResponse
    {
        $logs = $this->service->getScheduleLogs($scheduleId);
        return $this->successResponse($logs, 'Schedule maintenance logs retrieved successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/maintenance-logs/date-range",
     *     summary="Get maintenance logs within a date range",
     *     tags={"Maintenance Logs"},
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
     *         description="List of maintenance logs within the date range",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Maintenance logs retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/MaintenanceLog"))
     *         )
     *     )
     * )
     */
    public function getByDateRange(Request $request): JsonResponse
    {
        $logs = $this->service->getLogsByDateRange(
            $request->input('start_date'),
            $request->input('end_date')
        );
        return $this->successResponse($logs, 'Maintenance logs retrieved successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/maintenance-logs/type/{type}",
     *     summary="Get maintenance logs by type",
     *     tags={"Maintenance Logs"},
     *     @OA\Parameter(
     *         name="type",
     *         in="path",
     *         description="Maintenance type",
     *         required=true,
     *         @OA\Schema(type="string", enum={"preventive", "corrective", "emergency"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of maintenance logs of the specified type",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Maintenance logs retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/MaintenanceLog"))
     *         )
     *     )
     * )
     */
    public function getByType(string $type): JsonResponse
    {
        $logs = $this->service->getLogsByType($type);
        return $this->successResponse($logs, 'Maintenance logs retrieved successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/maintenance-logs/status/{status}",
     *     summary="Get maintenance logs by status",
     *     tags={"Maintenance Logs"},
     *     @OA\Parameter(
     *         name="status",
     *         in="path",
     *         description="Maintenance status",
     *         required=true,
     *         @OA\Schema(type="string", enum={"pending", "in_progress", "completed", "cancelled"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of maintenance logs with the specified status",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Maintenance logs retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/MaintenanceLog"))
     *         )
     *     )
     * )
     */
    public function getByStatus(string $status): JsonResponse
    {
        $logs = $this->service->getLogsByStatus($status);
        return $this->successResponse($logs, 'Maintenance logs retrieved successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/maintenance-logs/technician/{technicianId}",
     *     summary="Get maintenance logs by technician",
     *     tags={"Maintenance Logs"},
     *     @OA\Parameter(
     *         name="technicianId",
     *         in="path",
     *         description="Technician ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of maintenance logs performed by the technician",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Maintenance logs retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/MaintenanceLog"))
     *         )
     *     )
     * )
     */
    public function getByTechnician(int $technicianId): JsonResponse
    {
        $logs = $this->service->getLogsByTechnician($technicianId);
        return $this->successResponse($logs, 'Maintenance logs retrieved successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/maintenance-logs/{id}/attachments",
     *     summary="Add an attachment to a maintenance log",
     *     tags={"Maintenance Logs"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Maintenance log ID",
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
     *     path="/api/maintenance-logs/{id}/attachments/{attachmentId}",
     *     summary="Remove an attachment from a maintenance log",
     *     tags={"Maintenance Logs"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Maintenance log ID",
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
     *     path="/api/maintenance-logs/{id}/status",
     *     summary="Update maintenance log status",
     *     tags={"Maintenance Logs"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Maintenance log ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"status"},
     *             @OA\Property(property="status", type="string", enum={"pending", "in_progress", "completed", "cancelled"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Maintenance log status updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Maintenance log status updated successfully")
     *         )
     *     )
     * )
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $this->service->updateLogStatus($id, $request->input('status'));
        return $this->successResponse(null, 'Maintenance log status updated successfully');
    }
} 