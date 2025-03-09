<?php

namespace App\Listeners\WorkOrder;

use App\Events\WorkOrder\WorkOrderStatusChanged;
use App\Services\LoggingService;
use Illuminate\Support\Facades\Notification;

class NotifyWorkOrderStatusChange
{
    /**
     * Handle the event.
     */
    public function handle(WorkOrderStatusChanged $event): void
    {
        // Log the status change
        LoggingService::logWorkOrder(
            'work_order_status_changed',
            [
                'work_order_id' => $event->workOrder->id,
                'old_status' => $event->oldStatus,
                'new_status' => $event->newStatus,
                'changed_by' => auth()->id()
            ],
            auth()->id()
        );

        // Collect users to notify
        $usersToNotify = collect();

        // Always notify the reporter
        if ($event->workOrder->reported_by) {
            $usersToNotify->push($event->workOrder->reportedBy);
        }

        // Notify assigned technician if exists
        if ($event->workOrder->assigned_to) {
            $usersToNotify->push($event->workOrder->assignedTo);
        }

        // Notify supervisors if work order is completed or cancelled
        if (in_array($event->newStatus, ['completed', 'cancelled'])) {
            $supervisors = \App\Models\User::role('Maintenance Supervisor')->get();
            $usersToNotify = $usersToNotify->merge($supervisors);
        }

        // Send notifications
        $usersToNotify->unique()->each(function ($user) use ($event) {
            // You would create a proper notification class for this
            // Notification::send($user, new WorkOrderStatusChangedNotification($event->workOrder));
            
            // For now, just log it
            LoggingService::logSystemEvent('notification_queued', [
                'user_id' => $user->id,
                'type' => 'work_order_status_changed',
                'work_order_id' => $event->workOrder->id,
                'old_status' => $event->oldStatus,
                'new_status' => $event->newStatus
            ]);
        });
    }
} 