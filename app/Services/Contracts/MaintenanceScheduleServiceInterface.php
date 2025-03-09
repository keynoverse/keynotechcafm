<?php

namespace App\Services\Contracts;

use App\Models\MaintenanceSchedule;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface MaintenanceScheduleServiceInterface
{
    public function getAllSchedules(): Collection;
    
    public function getPaginatedSchedules(int $perPage = 15): LengthAwarePaginator;
    
    public function getScheduleById(int $id): ?MaintenanceSchedule;
    
    public function createSchedule(array $data): MaintenanceSchedule;
    
    public function updateSchedule(int $id, array $data): ?MaintenanceSchedule;
    
    public function deleteSchedule(int $id): bool;
    
    public function getAssetSchedules(int $assetId): Collection;
    
    public function getUpcomingSchedules(int $days = 7): Collection;
    
    public function getOverdueSchedules(): Collection;
    
    public function completeSchedule(int $id, array $data): bool;
    
    public function rescheduleMaintenanceTask(int $id, string $newDate): bool;
    
    public function getSchedulesByDateRange(string $startDate, string $endDate): Collection;
    
    public function getSchedulesByStatus(string $status): Collection;
    
    public function updateScheduleStatus(int $id, string $status): bool;
} 