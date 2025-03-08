<?php

namespace App\Repositories;

use App\Models\MaintenanceLog;
use App\Repositories\Contracts\MaintenanceLogRepositoryInterface;
use Carbon\Carbon;

class MaintenanceLogRepository extends BaseRepository implements MaintenanceLogRepositoryInterface
{
    public function __construct(MaintenanceLog $model)
    {
        parent::__construct($model);
    }

    public function getLogsByAsset($assetId)
    {
        return $this->model->where('asset_id', $assetId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getLogsBySchedule($scheduleId)
    {
        return $this->model->where('schedule_id', $scheduleId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getLogsByPerformer($userId)
    {
        return $this->model->where('performed_by', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getRecentLogs($days = 30)
    {
        return $this->model->where('created_at', '>=', Carbon::now()->subDays($days))
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getLogsByStatus($status)
    {
        return $this->model->where('status', $status)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getLogsByType($type)
    {
        return $this->model->where('type', $type)
            ->orderBy('created_at', 'desc')
            ->get();
    }
} 