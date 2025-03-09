<?php

namespace App\Listeners\WorkOrder;

use App\Events\WorkOrder\WorkOrderCreated;
use App\Services\LoggingService;

class LogWorkOrderCreation
{
    /**
     * Handle the event.
     */
    public function handle(WorkOrderCreated $event): void
    {
        LoggingService::logWorkOrder(
            'work_order_created',
            [
                'work_order_id' => $event->workOrder->id,
                'title' => $event->workOrder->title,
                'type' => $event->workOrder->type,
                'priority' => $event->workOrder->priority,
                'reported_by' => $event->workOrder->reported_by,
                'assigned_to' => $event->workOrder->assigned_to
            ],
            $event->workOrder->reported_by
        );
    }
} 