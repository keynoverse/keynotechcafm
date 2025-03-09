<?php

namespace App\Events\Maintenance;

use App\Models\MaintenanceSchedule;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MaintenanceScheduleCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public MaintenanceSchedule $schedule;

    /**
     * Create a new event instance.
     */
    public function __construct(MaintenanceSchedule $schedule)
    {
        $this->schedule = $schedule;
    }
} 