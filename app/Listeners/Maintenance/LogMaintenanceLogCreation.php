<?php

namespace App\Listeners\Maintenance;

use App\Events\Maintenance\MaintenanceLogCreated;
use App\Services\LoggingService;

class LogMaintenanceLogCreation
{
    /**
     * Handle the event.
     */
    public function handle(MaintenanceLogCreated $event): void
    {
        LoggingService::logMaintenance(
            'maintenance_log_created',
            [
                'log_id' => $event->log->id,
                'asset_id' => $event->log->asset_id,
                'schedule_id' => $event->log->schedule_id,
                'type' => $event->log->type,
                'title' => $event->log->title,
                'performed_by' => $event->log->performed_by,
                'performed_at' => $event->log->performed_at,
                'duration' => $event->log->duration,
                'duration_unit' => $event->log->duration_unit,
                'cost' => $event->log->cost
            ],
            $event->log->performed_by
        );

        // Update asset's last maintenance date
        $event->log->asset->update([
            'last_maintenance_date' => $event->log->performed_at
        ]);

        // If this is for a scheduled maintenance, update the schedule status
        if ($event->log->schedule_id) {
            $event->log->schedule->update([
                'status' => 'completed',
                'completion_date' => $event->log->performed_at,
                'completion_notes' => $event->log->description,
                'completed_by' => $event->log->performed_by
            ]);

            LoggingService::logMaintenance(
                'maintenance_schedule_completed',
                [
                    'schedule_id' => $event->log->schedule_id,
                    'completed_by' => $event->log->performed_by,
                    'completion_date' => $event->log->performed_at
                ],
                $event->log->performed_by
            );
        }
    }
} 