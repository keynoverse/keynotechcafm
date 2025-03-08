<?php

namespace App\Repositories\Contracts;

interface MaintenanceScheduleRepositoryInterface extends BaseRepositoryInterface
{
    public function getSchedulesByAsset($assetId);
    public function getUpcomingSchedules($days = 30);
    public function getOverdueSchedules();
    public function getScheduleWithLogs($id);
    public function updateLastMaintenanceDate($id, $date);
    public function updateNextMaintenanceDate($id, $date);
} 