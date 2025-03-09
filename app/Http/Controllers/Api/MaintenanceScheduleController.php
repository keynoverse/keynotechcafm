<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\MaintenanceSchedule\CreateMaintenanceScheduleRequest;
use App\Http\Requests\MaintenanceSchedule\UpdateMaintenanceScheduleRequest;
use App\Services\Contracts\MaintenanceScheduleServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Maintenance Schedules",
 *     description="API Endpoints for managing maintenance schedules"
 * )
 */
class MaintenanceScheduleController extends BaseApiController
{
    protected $service;

    public function __construct(MaintenanceScheduleServiceInterface $service)
    {
        $this->service = $service;
    }

    /**
     * @OA\Get(
     *     path="/api/maintenance-schedules",
     *     summary="Get all maintenance schedules",
     *     tags={"Maintenance Schedules"},
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
     *         description="List of maintenance schedules",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Maintenance schedules retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/MaintenanceSchedule"))
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 15);
        $schedules = $this->service->getPaginatedSchedules($perPage);
        return $this->successResponse($schedules, 'Maintenance schedules retrieved successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/maintenance-schedules",
     *     summary="Create a new maintenance schedule",
     *     tags={"Maintenance Schedules"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/MaintenanceSchedule")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Maintenance schedule created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Maintenance schedule created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/MaintenanceSchedule")
     *         )
     *     )
     * )
     */
    public function store(CreateMaintenanceScheduleRequest $request): JsonResponse
    {
        $schedule = $this->service->createSchedule($request->validated());
        return $this->successResponse($schedule, 'Maintenance schedule created successfully', 201);
    }

    /**
     * @OA\Get(
     *     path="/api/maintenance-schedules/{id}",
     *     summary="Get a maintenance schedule by ID",
     *     tags={"Maintenance Schedules"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Maintenance schedule ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Maintenance schedule details",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Maintenance schedule retrieved successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/MaintenanceSchedule")
     *         )
     *     )
     * )
     */
    public function show(int $id): JsonResponse
    {
        $schedule = $this->service->getScheduleById($id);
        return $this->successResponse($schedule, 'Maintenance schedule retrieved successfully');
    }

    /**
     * @OA\Put(
     *     path="/api/maintenance-schedules/{id}",
     *     summary="Update a maintenance schedule",
     *     tags={"Maintenance Schedules"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Maintenance schedule ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/MaintenanceSchedule")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Maintenance schedule updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Maintenance schedule updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/MaintenanceSchedule")
     *         )
     *     )
     * )
     */
    public function update(UpdateMaintenanceScheduleRequest $request, int $id): JsonResponse
    {
        $schedule = $this->service->updateSchedule($id, $request->validated());
        return $this->successResponse($schedule, 'Maintenance schedule updated successfully');
    }

    /**
     * @OA\Delete(
     *     path="/api/maintenance-schedules/{id}",
     *     summary="Delete a maintenance schedule",
     *     tags={"Maintenance Schedules"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Maintenance schedule ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Maintenance schedule deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Maintenance schedule deleted successfully")
     *         )
     *     )
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        $this->service->deleteSchedule($id);
        return $this->successResponse(null, 'Maintenance schedule deleted successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/maintenance-schedules/asset/{assetId}",
     *     summary="Get maintenance schedules for an asset",
     *     tags={"Maintenance Schedules"},
     *     @OA\Parameter(
     *         name="assetId",
     *         in="path",
     *         description="Asset ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of maintenance schedules for the asset",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Asset maintenance schedules retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/MaintenanceSchedule"))
     *         )
     *     )
     * )
     */
    public function getAssetSchedules(int $assetId): JsonResponse
    {
        $schedules = $this->service->getAssetSchedules($assetId);
        return $this->successResponse($schedules, 'Asset maintenance schedules retrieved successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/maintenance-schedules/upcoming",
     *     summary="Get upcoming maintenance schedules",
     *     tags={"Maintenance Schedules"},
     *     @OA\Parameter(
     *         name="days",
     *         in="query",
     *         description="Number of days to look ahead",
     *         required=false,
     *         @OA\Schema(type="integer", default=7)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of upcoming maintenance schedules",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Upcoming maintenance schedules retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/MaintenanceSchedule"))
     *         )
     *     )
     * )
     */
    public function upcoming(Request $request): JsonResponse
    {
        $days = $request->input('days', 7);
        $schedules = $this->service->getUpcomingSchedules($days);
        return $this->successResponse($schedules, 'Upcoming maintenance schedules retrieved successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/maintenance-schedules/overdue",
     *     summary="Get overdue maintenance schedules",
     *     tags={"Maintenance Schedules"},
     *     @OA\Response(
     *         response=200,
     *         description="List of overdue maintenance schedules",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Overdue maintenance schedules retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/MaintenanceSchedule"))
     *         )
     *     )
     * )
     */
    public function overdue(): JsonResponse
    {
        $schedules = $this->service->getOverdueSchedules();
        return $this->successResponse($schedules, 'Overdue maintenance schedules retrieved successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/maintenance-schedules/{id}/complete",
     *     summary="Complete a maintenance schedule",
     *     tags={"Maintenance Schedules"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Maintenance schedule ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"completion_date", "completed_by"},
     *             @OA\Property(property="completion_date", type="string", format="date-time"),
     *             @OA\Property(property="completion_notes", type="string"),
     *             @OA\Property(property="completed_by", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Maintenance schedule completed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Maintenance schedule completed successfully")
     *         )
     *     )
     * )
     */
    public function complete(Request $request, int $id): JsonResponse
    {
        $this->service->completeSchedule($id, $request->all());
        return $this->successResponse(null, 'Maintenance schedule completed successfully');
    }

    /**
     * @OA\Patch(
     *     path="/api/maintenance-schedules/{id}/reschedule",
     *     summary="Reschedule a maintenance task",
     *     tags={"Maintenance Schedules"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Maintenance schedule ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"scheduled_date"},
     *             @OA\Property(property="scheduled_date", type="string", format="date-time")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Maintenance task rescheduled successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Maintenance task rescheduled successfully")
     *         )
     *     )
     * )
     */
    public function reschedule(Request $request, int $id): JsonResponse
    {
        $this->service->rescheduleMaintenanceTask($id, $request->input('scheduled_date'));
        return $this->successResponse(null, 'Maintenance task rescheduled successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/maintenance-schedules/date-range",
     *     summary="Get maintenance schedules within a date range",
     *     tags={"Maintenance Schedules"},
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
     *         description="List of maintenance schedules within the date range",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Maintenance schedules retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/MaintenanceSchedule"))
     *         )
     *     )
     * )
     */
    public function getByDateRange(Request $request): JsonResponse
    {
        $schedules = $this->service->getSchedulesByDateRange(
            $request->input('start_date'),
            $request->input('end_date')
        );
        return $this->successResponse($schedules, 'Maintenance schedules retrieved successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/maintenance-schedules/status/{status}",
     *     summary="Get maintenance schedules by status",
     *     tags={"Maintenance Schedules"},
     *     @OA\Parameter(
     *         name="status",
     *         in="path",
     *         description="Schedule status",
     *         required=true,
     *         @OA\Schema(type="string", enum={"scheduled", "completed", "cancelled", "overdue"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of maintenance schedules with the specified status",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Maintenance schedules retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/MaintenanceSchedule"))
     *         )
     *     )
     * )
     */
    public function getByStatus(string $status): JsonResponse
    {
        $schedules = $this->service->getSchedulesByStatus($status);
        return $this->successResponse($schedules, 'Maintenance schedules retrieved successfully');
    }

    /**
     * @OA\Patch(
     *     path="/api/maintenance-schedules/{id}/status",
     *     summary="Update maintenance schedule status",
     *     tags={"Maintenance Schedules"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Maintenance schedule ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"status"},
     *             @OA\Property(property="status", type="string", enum={"scheduled", "completed", "cancelled", "overdue"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Maintenance schedule status updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Maintenance schedule status updated successfully")
     *         )
     *     )
     * )
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $this->service->updateScheduleStatus($id, $request->input('status'));
        return $this->successResponse(null, 'Maintenance schedule status updated successfully');
    }
} 