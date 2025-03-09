<?php

namespace App\Providers;

use App\Events\Maintenance\MaintenanceLogCreated;
use App\Events\Maintenance\MaintenanceScheduleCreated;
use App\Events\WorkOrder\WorkOrderCreated;
use App\Events\WorkOrder\WorkOrderStatusChanged;
use App\Listeners\Maintenance\LogMaintenanceLogCreation;
use App\Listeners\Maintenance\LogMaintenanceScheduleCreation;
use App\Listeners\WorkOrder\LogWorkOrderCreation;
use App\Listeners\WorkOrder\NotifyWorkOrderStatusChange;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],

        // Work Order Events
        WorkOrderCreated::class => [
            LogWorkOrderCreation::class,
        ],
        WorkOrderStatusChanged::class => [
            NotifyWorkOrderStatusChange::class,
        ],

        // Maintenance Events
        MaintenanceScheduleCreated::class => [
            LogMaintenanceScheduleCreation::class,
        ],
        MaintenanceLogCreated::class => [
            LogMaintenanceLogCreation::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
} 