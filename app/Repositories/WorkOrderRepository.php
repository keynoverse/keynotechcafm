<?php

namespace App\Repositories;

use App\Models\WorkOrder;
use App\Repositories\Contracts\WorkOrderRepositoryInterface;
use Carbon\Carbon;

class WorkOrderRepository extends BaseRepository implements WorkOrderRepositoryInterface
{
    public function __construct(WorkOrder $model)
    {
        parent::__construct($model);
    }

    public function findByNumber(string $number)
    {
        return $this->model->where('number', $number)->first();
    }

    public function getWorkOrdersByStatus($status)
    {
        return $this->model->where('status', $status)
            ->orderBy('due_date')
            ->get();
    }

    public function getWorkOrdersByPriority($priority)
    {
        return $this->model->where('priority', $priority)
            ->orderBy('due_date')
            ->get();
    }

    public function getWorkOrdersByAsset($assetId)
    {
        return $this->model->where('asset_id', $assetId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getWorkOrdersBySpace($spaceId)
    {
        return $this->model->where('space_id', $spaceId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getWorkOrdersByRequestor($userId)
    {
        return $this->model->where('requested_by', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getWorkOrdersByAssignee($userId)
    {
        return $this->model->where('assigned_to', $userId)
            ->orderBy('due_date')
            ->get();
    }

    public function getOverdueWorkOrders()
    {
        return $this->model->whereNotNull('due_date')
            ->whereNull('completed_at')
            ->where('due_date', '<', Carbon::now())
            ->orderBy('due_date')
            ->get();
    }

    public function getDueWorkOrders($days = 7)
    {
        $futureDate = Carbon::now()->addDays($days);
        return $this->model->whereNotNull('due_date')
            ->whereNull('completed_at')
            ->whereBetween('due_date', [Carbon::now(), $futureDate])
            ->orderBy('due_date')
            ->get();
    }

    public function getWorkOrderWithComments($id)
    {
        return $this->model->with('comments.user')->findOrFail($id);
    }

    public function getWorkOrderWithAttachments($id)
    {
        return $this->model->with('attachments')->findOrFail($id);
    }

    public function getWorkOrderWithAll($id)
    {
        return $this->model->with([
            'requester',
            'assignee',
            'asset',
            'space',
            'comments.user',
            'attachments'
        ])->findOrFail($id);
    }
} 