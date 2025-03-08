<?php

namespace App\Repositories;

use App\Models\WorkOrderComment;
use App\Repositories\Contracts\WorkOrderCommentRepositoryInterface;
use Carbon\Carbon;

class WorkOrderCommentRepository extends BaseRepository implements WorkOrderCommentRepositoryInterface
{
    public function __construct(WorkOrderComment $model)
    {
        parent::__construct($model);
    }

    public function getCommentsByWorkOrder($workOrderId)
    {
        return $this->model->where('work_order_id', $workOrderId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getCommentsByUser($userId)
    {
        return $this->model->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getRecentComments($days = 7)
    {
        return $this->model->where('created_at', '>=', Carbon::now()->subDays($days))
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getCommentWithUser($id)
    {
        return $this->model->with('user')->findOrFail($id);
    }
} 