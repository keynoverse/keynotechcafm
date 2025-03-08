<?php

namespace App\Repositories\Contracts;

interface MaintenanceLogRepositoryInterface extends BaseRepositoryInterface
{
    public function getLogsByAsset($assetId);
    public function getLogsBySchedule($scheduleId);
    public function getLogsByPerformer($userId);
    public function getRecentLogs($days = 30);
    public function getLogsByStatus($status);
    public function getLogsByType($type);
} 