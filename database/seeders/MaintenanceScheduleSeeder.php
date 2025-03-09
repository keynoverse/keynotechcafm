<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\MaintenanceSchedule;
use App\Models\User;
use Illuminate\Database\Seeder;

class MaintenanceScheduleSeeder extends Seeder
{
    public function run(): void
    {
        // Get maintenance technicians
        $technicians = User::role('Maintenance Technician')->get();
        
        if ($technicians->isEmpty()) {
            throw new \RuntimeException('No maintenance technicians found. Please run UserSeeder first.');
        }

        // Get all active assets
        $assets = Asset::where('status', 'active')->get();

        foreach ($assets as $asset) {
            // Create regular maintenance schedule based on asset criticality
            if ($asset->criticality === 'high') {
                // High criticality assets get monthly maintenance
                $this->createMonthlySchedule($asset, $technicians);
            } elseif ($asset->criticality === 'medium') {
                // Medium criticality assets get quarterly maintenance
                $this->createQuarterlySchedule($asset, $technicians);
            } else {
                // Low criticality assets get semi-annual maintenance
                $this->createSemiAnnualSchedule($asset, $technicians);
            }

            // Create some completed maintenance schedules
            $this->createCompletedSchedules($asset, $technicians);

            // Create some overdue maintenance schedules
            if (rand(1, 10) > 7) { // 30% chance
                $this->createOverdueSchedule($asset, $technicians);
            }
        }

        // Create some cancelled schedules
        MaintenanceSchedule::factory()
            ->count(10)
            ->cancelled()
            ->create();
    }

    private function createMonthlySchedule(Asset $asset, $technicians): void
    {
        for ($i = 1; $i <= 12; $i++) {
            MaintenanceSchedule::factory()
                ->forAsset($asset)
                ->assignedTo($technicians->random())
                ->create([
                    'scheduled_date' => now()->addMonths($i),
                    'frequency' => 1,
                    'frequency_unit' => 'months'
                ]);
        }
    }

    private function createQuarterlySchedule(Asset $asset, $technicians): void
    {
        for ($i = 1; $i <= 4; $i++) {
            MaintenanceSchedule::factory()
                ->forAsset($asset)
                ->assignedTo($technicians->random())
                ->create([
                    'scheduled_date' => now()->addMonths($i * 3),
                    'frequency' => 3,
                    'frequency_unit' => 'months'
                ]);
        }
    }

    private function createSemiAnnualSchedule(Asset $asset, $technicians): void
    {
        for ($i = 1; $i <= 2; $i++) {
            MaintenanceSchedule::factory()
                ->forAsset($asset)
                ->assignedTo($technicians->random())
                ->create([
                    'scheduled_date' => now()->addMonths($i * 6),
                    'frequency' => 6,
                    'frequency_unit' => 'months'
                ]);
        }
    }

    private function createCompletedSchedules(Asset $asset, $technicians): void
    {
        // Create 2-5 completed maintenance schedules in the past
        $count = rand(2, 5);
        for ($i = $count; $i > 0; $i--) {
            MaintenanceSchedule::factory()
                ->forAsset($asset)
                ->assignedTo($technicians->random())
                ->completed()
                ->create([
                    'scheduled_date' => now()->subMonths($i),
                    'completion_date' => now()->subMonths($i)->addDays(rand(0, 5))
                ]);
        }
    }

    private function createOverdueSchedule(Asset $asset, $technicians): void
    {
        MaintenanceSchedule::factory()
            ->forAsset($asset)
            ->assignedTo($technicians->random())
            ->overdue()
            ->create([
                'scheduled_date' => now()->subDays(rand(5, 30))
            ]);
    }
} 