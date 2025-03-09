<?php

namespace App\Listeners\Maintenance;

use App\Events\Maintenance\MaintenanceScheduleCreated;
use App\Services\LoggingService;

class LogMaintenanceScheduleCreation
{
    /**
     * Handle the event.
     */
    public function handle(MaintenanceScheduleCreated $event): void
    {
        LoggingService::logMaintenance(
            'maintenance_schedule_created',
            [
                'schedule_id' => $event->schedule->id,
                'asset_id' => $event->schedule->asset_id,
                'title' => $event->schedule->title,
                'scheduled_date' => $event->schedule->scheduled_date,
                'frequency' => $event->schedule->frequency,
                'frequency_unit' => $event->schedule->frequency_unit,
                'priority' => $event->schedule->priority,
                'assigned_to' => $event->schedule->assigned_to
            ],
            auth()->id()
        );

        // Update asset's next maintenance date
        $event->schedule->asset->update([
            'next_maintenance_date' => $event->schedule->scheduled_date
        ]);

        LoggingService::logSystemEvent('asset_maintenance_date_updated', [
            'asset_id' => $event->schedule->asset_id,
            'next_maintenance_date' => $event->schedule->scheduled_date
        ]);
    }
} 