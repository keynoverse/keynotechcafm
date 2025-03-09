<?php

namespace App\Events\Maintenance;

use App\Models\MaintenanceLog;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MaintenanceLogCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public MaintenanceLog $log;

    /**
     * Create a new event instance.
     */
    public function __construct(MaintenanceLog $log)
    {
        $this->log = $log;
    }
} 