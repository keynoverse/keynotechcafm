<?php

namespace App\Services;

use App\Models\MaintenanceLog;
use App\Repositories\Contracts\MaintenanceLogRepositoryInterface;
use App\Services\Contracts\MaintenanceLogServiceInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class MaintenanceLogService extends BaseService implements MaintenanceLogServiceInterface
{
    protected $repository;

    public function __construct(MaintenanceLogRepositoryInterface $repository)
    {
        parent::__construct();
        $this->repository = $repository;
    }

    public function getValidationRules(string $action = 'create'): array
    {
        $rules = [
            'asset_id' => 'required|exists:assets,id',
            'schedule_id' => 'nullable|exists:maintenance_schedules,id',
            'type' => 'required|in:preventive,corrective,emergency',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'performed_by' => 'required|exists:users,id',
            'performed_at' => 'required|date',
            'duration' => 'required|integer|min:1',
            'duration_unit' => 'required|in:minutes,hours',
            'cost' => 'required|numeric|min:0',
            'parts_used' => 'nullable|array',
            'status' => 'required|in:pending,in_progress,completed,cancelled',
            'notes' => 'nullable|string',
            'metadata' => 'nullable|array'
        ];

        if ($action === 'update') {
            $rules['id'] = 'required|exists:maintenance_logs,id';
            // Make all fields optional for update
            foreach ($rules as $field => $rule) {
                if ($field !== 'id') {
                    $rules[$field] = str_replace('required|', '', $rule);
                }
            }
        }

        return $rules;
    }

    public function getAllLogs(): Collection
    {
        return $this->repository->all();
    }

    public function getPaginatedLogs(int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginate($perPage);
    }

    public function getLogById(int $id): ?MaintenanceLog
    {
        return $this->repository->find($id);
    }

    public function createLog(array $data): MaintenanceLog
    {
        $this->validate($data, $this->getValidationRules());
        return $this->repository->create($data);
    }

    public function updateLog(int $id, array $data): ?MaintenanceLog
    {
        $data['id'] = $id;
        $this->validate($data, $this->getValidationRules('update'));
        return $this->repository->update($id, $data);
    }

    public function deleteLog(int $id): bool
    {
        return $this->repository->delete($id);
    }

    public function getAssetLogs(int $assetId): Collection
    {
        return $this->repository->findByField('asset_id', $assetId);
    }

    public function getScheduleLogs(int $scheduleId): Collection
    {
        return $this->repository->findByField('schedule_id', $scheduleId);
    }

    public function getLogsByDateRange(string $startDate, string $endDate): Collection
    {
        $this->validate([
            'start_date' => $startDate,
            'end_date' => $endDate
        ], [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date'
        ]);

        return $this->repository->findWhere([
            ['performed_at', '>=', $startDate],
            ['performed_at', '<=', $endDate]
        ]);
    }

    public function getLogsByType(string $type): Collection
    {
        $this->validate(['type' => $type], [
            'type' => 'required|in:preventive,corrective,emergency'
        ]);

        return $this->repository->findByField('type', $type);
    }

    public function getLogsByStatus(string $status): Collection
    {
        $this->validate(['status' => $status], [
            'status' => 'required|in:pending,in_progress,completed,cancelled'
        ]);

        return $this->repository->findByField('status', $status);
    }

    public function getLogsByTechnician(int $technicianId): Collection
    {
        $this->validate(['technician_id' => $technicianId], [
            'technician_id' => 'required|exists:users,id'
        ]);

        return $this->repository->findByField('performed_by', $technicianId);
    }

    public function addAttachment(int $logId, array $attachmentData): bool
    {
        $this->validate($attachmentData, [
            'file_name' => 'required|string|max:255',
            'file_path' => 'required|string',
            'file_type' => 'required|string|max:50',
            'file_size' => 'required|integer',
            'description' => 'nullable|string'
        ]);

        $log = $this->getLogById($logId);
        if (!$log) {
            return false;
        }

        return $log->attachments()->create($attachmentData) !== null;
    }

    public function removeAttachment(int $logId, int $attachmentId): bool
    {
        $log = $this->getLogById($logId);
        if (!$log) {
            return false;
        }

        return $log->attachments()->where('id', $attachmentId)->delete() > 0;
    }

    public function updateLogStatus(int $id, string $status): bool
    {
        $this->validate(['status' => $status], [
            'status' => 'required|in:pending,in_progress,completed,cancelled'
        ]);

        return $this->repository->update($id, ['status' => $status]) !== null;
    }
} 