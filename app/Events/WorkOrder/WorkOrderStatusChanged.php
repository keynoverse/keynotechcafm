<?php

namespace App\Events\WorkOrder;

use App\Models\WorkOrder;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WorkOrderStatusChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public WorkOrder $workOrder;
    public string $oldStatus;
    public string $newStatus;

    /**
     * Create a new event instance.
     */
    public function __construct(WorkOrder $workOrder, string $oldStatus, string $newStatus)
    {
        $this->workOrder = $workOrder;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
    }
} 