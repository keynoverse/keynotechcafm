<?php

namespace App\Repositories;

use App\Models\MaintenanceSchedule;
use App\Repositories\Contracts\MaintenanceScheduleRepositoryInterface;
use Carbon\Carbon;

class MaintenanceScheduleRepository extends BaseRepository implements MaintenanceScheduleRepositoryInterface
{
    public function __construct(MaintenanceSchedule $model)
    {
        parent::__construct($model);
    }

    public function getSchedulesByAsset($assetId)
    {
        return $this->model->where('asset_id', $assetId)->get();
    }

    public function getUpcomingSchedules($days = 30)
    {
        $futureDate = Carbon::now()->addDays($days);
        return $this->model->whereDate('next_maintenance', '<=', $futureDate)
            ->whereDate('next_maintenance', '>=', Carbon::now())
            ->orderBy('next_maintenance')
            ->get();
    }

    public function getOverdueSchedules()
    {
        return $this->model->whereDate('next_maintenance', '<', Carbon::now())
            ->orderBy('next_maintenance')
            ->get();
    }

    public function getScheduleWithLogs($id)
    {
        return $this->model->with('maintenanceLogs')->findOrFail($id);
    }

    public function updateLastMaintenanceDate($id, $date)
    {
        $schedule = $this->findOrFail($id);
        $schedule->update(['last_maintenance' => $date]);
        return $schedule;
    }

    public function updateNextMaintenanceDate($id, $date)
    {
        $schedule = $this->findOrFail($id);
        $schedule->update(['next_maintenance' => $date]);
        return $schedule;
    }
} 