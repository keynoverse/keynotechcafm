<?php

namespace App\Services;

use App\Models\WorkOrder;
use App\Repositories\Contracts\WorkOrderRepositoryInterface;
use App\Services\Contracts\WorkOrderServiceInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class WorkOrderService extends BaseService implements WorkOrderServiceInterface
{
    protected $repository;

    public function __construct(WorkOrderRepositoryInterface $repository)
    {
        parent::__construct();
        $this->repository = $repository;
    }

    public function getValidationRules(string $action = 'create'): array
    {
        $rules = [
            'asset_id' => 'required|exists:assets,id',
            'space_id' => 'nullable|exists:spaces,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|in:corrective,preventive,emergency,inspection',
            'priority' => 'required|in:low,medium,high,critical',
            'status' => 'required|in:pending,assigned,in_progress,on_hold,completed,cancelled',
            'requested_by' => 'required|exists:users,id',
            'assigned_to' => 'nullable|exists:users,id',
            'due_date' => 'required|date|after:now',
            'estimated_hours' => 'required|numeric|min:0',
            'actual_hours' => 'nullable|numeric|min:0',
            'completion_date' => 'nullable|date',
            'completion_notes' => 'nullable|string',
            'cost' => 'nullable|numeric|min:0',
            'parts_used' => 'nullable|array',
            'metadata' => 'nullable|array'
        ];

        if ($action === 'update') {
            $rules['id'] = 'required|exists:work_orders,id';
            // Make all fields optional for update
            foreach ($rules as $field => $rule) {
                if ($field !== 'id') {
                    $rules[$field] = str_replace('required|', '', $rule);
                }
            }
        }

        return $rules;
    }

    public function getAllWorkOrders(): Collection
    {
        return $this->repository->all();
    }

    public function getPaginatedWorkOrders(int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginate($perPage);
    }

    public function getWorkOrderById(int $id): ?WorkOrder
    {
        return $this->repository->find($id);
    }

    public function createWorkOrder(array $data): WorkOrder
    {
        $this->validate($data, $this->getValidationRules());
        return $this->repository->create($data);
    }

    public function updateWorkOrder(int $id, array $data): ?WorkOrder
    {
        $data['id'] = $id;
        $this->validate($data, $this->getValidationRules('update'));
        return $this->repository->update($id, $data);
    }

    public function deleteWorkOrder(int $id): bool
    {
        return $this->repository->delete($id);
    }

    public function getAssetWorkOrders(int $assetId): Collection
    {
        return $this->repository->findByField('asset_id', $assetId);
    }

    public function getSpaceWorkOrders(int $spaceId): Collection
    {
        return $this->repository->findByField('space_id', $spaceId);
    }

    public function getWorkOrdersByDateRange(string $startDate, string $endDate): Collection
    {
        $this->validate([
            'start_date' => $startDate,
            'end_date' => $endDate
        ], [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date'
        ]);

        return $this->repository->findWhere([
            ['created_at', '>=', $startDate],
            ['created_at', '<=', $endDate]
        ]);
    }

    public function getWorkOrdersByType(string $type): Collection
    {
        $this->validate(['type' => $type], [
            'type' => 'required|in:corrective,preventive,emergency,inspection'
        ]);

        return $this->repository->findByField('type', $type);
    }

    public function getWorkOrdersByPriority(string $priority): Collection
    {
        $this->validate(['priority' => $priority], [
            'priority' => 'required|in:low,medium,high,critical'
        ]);

        return $this->repository->findByField('priority', $priority);
    }

    public function getWorkOrdersByStatus(string $status): Collection
    {
        $this->validate(['status' => $status], [
            'status' => 'required|in:pending,assigned,in_progress,on_hold,completed,cancelled'
        ]);

        return $this->repository->findByField('status', $status);
    }

    public function getWorkOrdersByAssignee(int $assigneeId): Collection
    {
        return $this->repository->findByField('assigned_to', $assigneeId);
    }

    public function getWorkOrdersByRequestor(int $requestorId): Collection
    {
        return $this->repository->findByField('requested_by', $requestorId);
    }

    public function addComment(int $workOrderId, array $commentData): bool
    {
        $this->validate($commentData, [
            'user_id' => 'required|exists:users,id',
            'comment' => 'required|string',
            'metadata' => 'nullable|array'
        ]);

        $workOrder = $this->getWorkOrderById($workOrderId);
        if (!$workOrder) {
            return false;
        }

        return $workOrder->comments()->create($commentData) !== null;
    }

    public function removeComment(int $workOrderId, int $commentId): bool
    {
        $workOrder = $this->getWorkOrderById($workOrderId);
        if (!$workOrder) {
            return false;
        }

        return $workOrder->comments()->where('id', $commentId)->delete() > 0;
    }

    public function addAttachment(int $workOrderId, array $attachmentData): bool
    {
        $this->validate($attachmentData, [
            'file_name' => 'required|string|max:255',
            'file_path' => 'required|string',
            'file_type' => 'required|string|max:50',
            'file_size' => 'required|integer',
            'description' => 'nullable|string'
        ]);

        $workOrder = $this->getWorkOrderById($workOrderId);
        if (!$workOrder) {
            return false;
        }

        return $workOrder->attachments()->create($attachmentData) !== null;
    }

    public function removeAttachment(int $workOrderId, int $attachmentId): bool
    {
        $workOrder = $this->getWorkOrderById($workOrderId);
        if (!$workOrder) {
            return false;
        }

        return $workOrder->attachments()->where('id', $attachmentId)->delete() > 0;
    }

    public function updateWorkOrderStatus(int $id, string $status): bool
    {
        $this->validate(['status' => $status], [
            'status' => 'required|in:pending,assigned,in_progress,on_hold,completed,cancelled'
        ]);

        $data = ['status' => $status];
        if ($status === 'completed') {
            $data['completion_date'] = Carbon::now();
        }

        return $this->repository->update($id, $data) !== null;
    }

    public function assignWorkOrder(int $id, int $assigneeId): bool
    {
        $this->validate(['assignee_id' => $assigneeId], [
            'assignee_id' => 'required|exists:users,id'
        ]);

        return $this->repository->update($id, [
            'assigned_to' => $assigneeId,
            'status' => 'assigned'
        ]) !== null;
    }

    public function getWorkOrderStatistics(): array
    {
        $total = $this->repository->count();
        $pending = $this->repository->findByField('status', 'pending')->count();
        $inProgress = $this->repository->findByField('status', 'in_progress')->count();
        $completed = $this->repository->findByField('status', 'completed')->count();
        $overdue = $this->repository->findWhere([
            ['due_date', '<', Carbon::now()],
            ['status', 'not in', ['completed', 'cancelled']]
        ])->count();

        $byPriority = [
            'low' => $this->repository->findByField('priority', 'low')->count(),
            'medium' => $this->repository->findByField('priority', 'medium')->count(),
            'high' => $this->repository->findByField('priority', 'high')->count(),
            'critical' => $this->repository->findByField('priority', 'critical')->count()
        ];

        $byType = [
            'corrective' => $this->repository->findByField('type', 'corrective')->count(),
            'preventive' => $this->repository->findByField('type', 'preventive')->count(),
            'emergency' => $this->repository->findByField('type', 'emergency')->count(),
            'inspection' => $this->repository->findByField('type', 'inspection')->count()
        ];

        return [
            'total' => $total,
            'pending' => $pending,
            'in_progress' => $inProgress,
            'completed' => $completed,
            'overdue' => $overdue,
            'by_priority' => $byPriority,
            'by_type' => $byType,
            'completion_rate' => $total > 0 ? round(($completed / $total) * 100, 2) : 0
        ];
    }
} 