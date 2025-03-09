<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\Space;
use App\Models\User;
use App\Models\WorkOrder;
use App\Models\WorkOrderAttachment;
use App\Models\WorkOrderComment;
use Illuminate\Database\Seeder;

class WorkOrderSeeder extends Seeder
{
    public function run(): void
    {
        // Get users by role
        $technicians = User::role('Maintenance Technician')->get();
        $employees = User::role('Employee')->get();
        $supervisors = User::role('Maintenance Supervisor')->get();

        if ($technicians->isEmpty() || $employees->isEmpty() || $supervisors->isEmpty()) {
            throw new \RuntimeException('Required user roles not found. Please run UserSeeder first.');
        }

        // Create work orders for assets
        $assets = Asset::all();
        foreach ($assets as $asset) {
            // 40% chance of having a work order
            if (rand(1, 10) <= 4) {
                $this->createAssetWorkOrders($asset, $employees, $technicians, $supervisors);
            }
        }

        // Create work orders for spaces
        $spaces = Space::all();
        foreach ($spaces as $space) {
            // 30% chance of having a work order
            if (rand(1, 10) <= 3) {
                $this->createSpaceWorkOrders($space, $employees, $technicians, $supervisors);
            }
        }

        // Create some emergency work orders
        $this->createEmergencyWorkOrders($employees, $technicians, $supervisors);

        // Create some completed work orders
        $this->createCompletedWorkOrders($employees, $technicians, $supervisors);
    }

    private function createAssetWorkOrders($asset, $employees, $technicians, $supervisors): void
    {
        $workOrder = WorkOrder::factory()
            ->forAsset($asset)
            ->reportedBy($employees->random())
            ->assignedTo($technicians->random())
            ->create();

        $this->addCommentsAndAttachments($workOrder, $employees, $technicians, $supervisors);
    }

    private function createSpaceWorkOrders($space, $employees, $technicians, $supervisors): void
    {
        $workOrder = WorkOrder::factory()
            ->forSpace($space)
            ->reportedBy($employees->random())
            ->assignedTo($technicians->random())
            ->create();

        $this->addCommentsAndAttachments($workOrder, $employees, $technicians, $supervisors);
    }

    private function createEmergencyWorkOrders($employees, $technicians, $supervisors): void
    {
        // Create 5-10 emergency work orders
        $count = rand(5, 10);
        for ($i = 0; $i < $count; $i++) {
            $workOrder = WorkOrder::factory()
                ->urgent()
                ->reportedBy($employees->random())
                ->assignedTo($technicians->random())
                ->create([
                    'type' => 'emergency'
                ]);

            // Emergency work orders get more comments
            $this->addCommentsAndAttachments($workOrder, $employees, $technicians, $supervisors, true);
        }
    }

    private function createCompletedWorkOrders($employees, $technicians, $supervisors): void
    {
        // Create 20-30 completed work orders
        $count = rand(20, 30);
        for ($i = 0; $i < $count; $i++) {
            $workOrder = WorkOrder::factory()
                ->completed()
                ->reportedBy($employees->random())
                ->assignedTo($technicians->random())
                ->create();

            $this->addCommentsAndAttachments($workOrder, $employees, $technicians, $supervisors);
        }
    }

    private function addCommentsAndAttachments($workOrder, $employees, $technicians, $supervisors, bool $isEmergency = false): void
    {
        // Add initial description comment
        WorkOrderComment::factory()
            ->forWorkOrder($workOrder)
            ->byUser($workOrder->reported_by)
            ->create([
                'type' => 'note',
                'comment' => 'Initial report: ' . $workOrder->description
            ]);

        // Add technician comments
        $commentCount = $isEmergency ? rand(4, 8) : rand(1, 4);
        for ($i = 0; $i < $commentCount; $i++) {
            WorkOrderComment::factory()
                ->forWorkOrder($workOrder)
                ->byUser($workOrder->assigned_to)
                ->update()
                ->create();
        }

        // Add supervisor comment (30% chance)
        if (rand(1, 10) <= 3) {
            WorkOrderComment::factory()
                ->forWorkOrder($workOrder)
                ->byUser($supervisors->random())
                ->create();
        }

        // Add attachments
        $attachmentCount = $isEmergency ? rand(2, 5) : rand(0, 3);
        for ($i = 0; $i < $attachmentCount; $i++) {
            WorkOrderAttachment::factory()
                ->forWorkOrder($workOrder)
                ->uploadedBy($workOrder->assigned_to)
                ->processed()
                ->create();
        }

        // Add completion comment if work order is completed
        if ($workOrder->status === 'completed') {
            WorkOrderComment::factory()
                ->forWorkOrder($workOrder)
                ->byUser($workOrder->assigned_to)
                ->resolution()
                ->create();

            // Add completion photos
            WorkOrderAttachment::factory()
                ->count(2)
                ->forWorkOrder($workOrder)
                ->uploadedBy($workOrder->assigned_to)
                ->image()
                ->processed()
                ->create();
        }
    }
} 