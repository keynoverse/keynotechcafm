<?php

namespace App\Repositories;

use App\Models\WorkOrderAttachment;
use App\Repositories\Contracts\WorkOrderAttachmentRepositoryInterface;
use Carbon\Carbon;

class WorkOrderAttachmentRepository extends BaseRepository implements WorkOrderAttachmentRepositoryInterface
{
    public function __construct(WorkOrderAttachment $model)
    {
        parent::__construct($model);
    }

    public function getAttachmentsByWorkOrder($workOrderId)
    {
        return $this->model->where('work_order_id', $workOrderId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getAttachmentsByType($type)
    {
        return $this->model->where('file_type', $type)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getRecentAttachments($days = 7)
    {
        return $this->model->where('created_at', '>=', Carbon::now()->subDays($days))
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function findByFileName($fileName)
    {
        return $this->model->where('file_name', $fileName)->first();
    }
} 