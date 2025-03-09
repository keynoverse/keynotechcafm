<?php

namespace App\Services\Contracts;

use App\Models\MaintenanceLog;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface MaintenanceLogServiceInterface
{
    public function getAllLogs(): Collection;
    
    public function getPaginatedLogs(int $perPage = 15): LengthAwarePaginator;
    
    public function getLogById(int $id): ?MaintenanceLog;
    
    public function createLog(array $data): MaintenanceLog;
    
    public function updateLog(int $id, array $data): ?MaintenanceLog;
    
    public function deleteLog(int $id): bool;
    
    public function getAssetLogs(int $assetId): Collection;
    
    public function getScheduleLogs(int $scheduleId): Collection;
    
    public function getLogsByDateRange(string $startDate, string $endDate): Collection;
    
    public function getLogsByType(string $type): Collection;
    
    public function getLogsByStatus(string $status): Collection;
    
    public function getLogsByTechnician(int $technicianId): Collection;
    
    public function addAttachment(int $logId, array $attachmentData): bool;
    
    public function removeAttachment(int $logId, int $attachmentId): bool;
    
    public function updateLogStatus(int $id, string $status): bool;
} 