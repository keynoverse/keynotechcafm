<?php

namespace App\Services;

use App\Models\MaintenanceSchedule;
use App\Repositories\Contracts\MaintenanceScheduleRepositoryInterface;
use App\Services\Contracts\MaintenanceScheduleServiceInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class MaintenanceScheduleService extends BaseService implements MaintenanceScheduleServiceInterface
{
    protected $repository;

    public function __construct(MaintenanceScheduleRepositoryInterface $repository)
    {
        parent::__construct();
        $this->repository = $repository;
    }

    public function getValidationRules(string $action = 'create'): array
    {
        $rules = [
            'asset_id' => 'required|exists:assets,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'scheduled_date' => 'required|date',
            'frequency' => 'required|integer',
            'frequency_unit' => 'required|in:days,weeks,months,years',
            'priority' => 'required|in:low,medium,high',
            'status' => 'required|in:scheduled,completed,cancelled,overdue',
            'assigned_to' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
            'metadata' => 'nullable|array'
        ];

        if ($action === 'update') {
            $rules['id'] = 'required|exists:maintenance_schedules,id';
            // Make all fields optional for update
            foreach ($rules as $field => $rule) {
                if ($field !== 'id') {
                    $rules[$field] = str_replace('required|', '', $rule);
                }
            }
        }

        return $rules;
    }

    public function getAllSchedules(): Collection
    {
        return $this->repository->all();
    }

    public function getPaginatedSchedules(int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginate($perPage);
    }

    public function getScheduleById(int $id): ?MaintenanceSchedule
    {
        return $this->repository->find($id);
    }

    public function createSchedule(array $data): MaintenanceSchedule
    {
        $this->validate($data, $this->getValidationRules());
        return $this->repository->create($data);
    }

    public function updateSchedule(int $id, array $data): ?MaintenanceSchedule
    {
        $data['id'] = $id;
        $this->validate($data, $this->getValidationRules('update'));
        return $this->repository->update($id, $data);
    }

    public function deleteSchedule(int $id): bool
    {
        return $this->repository->delete($id);
    }

    public function getAssetSchedules(int $assetId): Collection
    {
        return $this->repository->findByField('asset_id', $assetId);
    }

    public function getUpcomingSchedules(int $days = 7): Collection
    {
        $startDate = Carbon::now();
        $endDate = Carbon::now()->addDays($days);
        
        return $this->repository->findWhere([
            ['scheduled_date', '>=', $startDate],
            ['scheduled_date', '<=', $endDate],
            ['status', '=', 'scheduled']
        ]);
    }

    public function getOverdueSchedules(): Collection
    {
        return $this->repository->findWhere([
            ['scheduled_date', '<', Carbon::now()],
            ['status', '=', 'scheduled']
        ]);
    }

    public function completeSchedule(int $id, array $data): bool
    {
        $this->validate($data, [
            'completion_date' => 'required|date',
            'completion_notes' => 'nullable|string',
            'completed_by' => 'required|exists:users,id'
        ]);

        $schedule = $this->getScheduleById($id);
        if (!$schedule) {
            return false;
        }

        // Update the current schedule
        $updateData = [
            'status' => 'completed',
            'completion_date' => $data['completion_date'],
            'completion_notes' => $data['completion_notes'] ?? null,
            'completed_by' => $data['completed_by']
        ];

        $updated = $this->repository->update($id, $updateData);
        if (!$updated) {
            return false;
        }

        // Create next schedule based on frequency
        $nextDate = $this->calculateNextMaintenanceDate(
            $schedule->scheduled_date,
            $schedule->frequency,
            $schedule->frequency_unit
        );

        $newScheduleData = $schedule->toArray();
        unset($newScheduleData['id']);
        $newScheduleData['scheduled_date'] = $nextDate;
        $newScheduleData['status'] = 'scheduled';
        $newScheduleData['completion_date'] = null;
        $newScheduleData['completion_notes'] = null;
        $newScheduleData['completed_by'] = null;

        $this->repository->create($newScheduleData);

        return true;
    }

    public function rescheduleMaintenanceTask(int $id, string $newDate): bool
    {
        $this->validate(['scheduled_date' => $newDate], [
            'scheduled_date' => 'required|date|after:now'
        ]);

        return $this->repository->update($id, [
            'scheduled_date' => $newDate,
            'status' => 'scheduled'
        ]);
    }

    public function getSchedulesByDateRange(string $startDate, string $endDate): Collection
    {
        $this->validate([
            'start_date' => $startDate,
            'end_date' => $endDate
        ], [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date'
        ]);

        return $this->repository->findWhere([
            ['scheduled_date', '>=', $startDate],
            ['scheduled_date', '<=', $endDate]
        ]);
    }

    public function getSchedulesByStatus(string $status): Collection
    {
        $this->validate(['status' => $status], [
            'status' => 'required|in:scheduled,completed,cancelled,overdue'
        ]);

        return $this->repository->findByField('status', $status);
    }

    public function updateScheduleStatus(int $id, string $status): bool
    {
        $this->validate(['status' => $status], [
            'status' => 'required|in:scheduled,completed,cancelled,overdue'
        ]);

        return $this->repository->update($id, ['status' => $status]);
    }

    protected function calculateNextMaintenanceDate(string $currentDate, int $frequency, string $unit): string
    {
        $date = Carbon::parse($currentDate);
        
        switch ($unit) {
            case 'days':
                return $date->addDays($frequency);
            case 'weeks':
                return $date->addWeeks($frequency);
            case 'months':
                return $date->addMonths($frequency);
            case 'years':
                return $date->addYears($frequency);
            default:
                return $date->addDays($frequency);
        }
    }
} 