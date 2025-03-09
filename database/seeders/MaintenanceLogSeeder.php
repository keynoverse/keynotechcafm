<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\MaintenanceLog;
use App\Models\MaintenanceSchedule;
use App\Models\User;
use Illuminate\Database\Seeder;

class MaintenanceLogSeeder extends Seeder
{
    public function run(): void
    {
        // Get maintenance technicians
        $technicians = User::role('Maintenance Technician')->get();
        
        if ($technicians->isEmpty()) {
            throw new \RuntimeException('No maintenance technicians found. Please run UserSeeder first.');
        }

        // Create logs for completed maintenance schedules
        $completedSchedules = MaintenanceSchedule::where('status', 'completed')->get();
        foreach ($completedSchedules as $schedule) {
            MaintenanceLog::factory()
                ->forSchedule($schedule)
                ->performedBy($schedule->assigned_to)
                ->completed()
                ->create([
                    'performed_at' => $schedule->completion_date
                ]);
        }

        // Create emergency maintenance logs
        $assets = Asset::all();
        foreach ($assets as $asset) {
            // 20% chance of having emergency maintenance
            if (rand(1, 5) === 1) {
                $this->createEmergencyLogs($asset, $technicians);
            }
        }

        // Create corrective maintenance logs
        foreach ($assets as $asset) {
            // 30% chance of having corrective maintenance
            if (rand(1, 10) <= 3) {
                $this->createCorrectiveLogs($asset, $technicians);
            }
        }

        // Create preventive maintenance logs for high criticality assets
        $highCriticalityAssets = Asset::where('criticality', 'high')->get();
        foreach ($highCriticalityAssets as $asset) {
            $this->createPreventiveLogs($asset, $technicians);
        }

        // Create some pending maintenance logs
        MaintenanceLog::factory()
            ->count(5)
            ->state(function (array $attributes) {
                return [
                    'status' => 'pending'
                ];
            })
            ->create();

        // Create some in-progress maintenance logs
        MaintenanceLog::factory()
            ->count(3)
            ->state(function (array $attributes) {
                return [
                    'status' => 'in_progress'
                ];
            })
            ->create();
    }

    private function createEmergencyLogs(Asset $asset, $technicians): void
    {
        $count = rand(1, 3);
        for ($i = 0; $i < $count; $i++) {
            MaintenanceLog::factory()
                ->emergency()
                ->forAsset($asset)
                ->performedBy($technicians->random())
                ->completed()
                ->create([
                    'performed_at' => now()->subMonths(rand(1, 6))
                        ->subDays(rand(0, 30))
                ]);
        }
    }

    private function createCorrectiveLogs(Asset $asset, $technicians): void
    {
        $count = rand(1, 5);
        for ($i = 0; $i < $count; $i++) {
            MaintenanceLog::factory()
                ->corrective()
                ->forAsset($asset)
                ->performedBy($technicians->random())
                ->completed()
                ->create([
                    'performed_at' => now()->subMonths(rand(1, 12))
                        ->subDays(rand(0, 30))
                ]);
        }
    }

    private function createPreventiveLogs(Asset $asset, $technicians): void
    {
        // Create monthly preventive maintenance logs for the past year
        for ($i = 1; $i <= 12; $i++) {
            MaintenanceLog::factory()
                ->preventive()
                ->forAsset($asset)
                ->performedBy($technicians->random())
                ->completed()
                ->create([
                    'performed_at' => now()->subMonths($i)
                        ->addDays(rand(-5, 5)) // Add some variance to the dates
                ]);
        }
    }
} 