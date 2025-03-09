<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleAndPermissionSeeder::class,
            UserSeeder::class,
            BuildingSeeder::class,
            FloorSeeder::class,
            SpaceSeeder::class,
            AssetCategorySeeder::class,
            AssetSeeder::class,
            MaintenanceScheduleSeeder::class,
            MaintenanceLogSeeder::class,
            WorkOrderSeeder::class,
        ]);
    }
}
