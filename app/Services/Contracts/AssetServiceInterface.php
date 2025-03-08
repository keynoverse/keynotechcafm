<?php

namespace App\Services\Contracts;

interface AssetServiceInterface extends BaseServiceInterface
{
    public function findByCode(string $code);
    public function getActiveAssets();
    public function getAssetsBySpace($spaceId);
    public function getAssetsByFloor($floorId);
    public function getAssetsByBuilding($buildingId);
    public function getAssetsByCategory($categoryId);
    public function updateStatus($id, string $status);
    public function assignToSpace($id, $spaceId);
    public function getMaintenanceHistory($id);
    public function getWorkOrderHistory($id);
    public function getAssetUtilization($id);
    public function getAssetWithMaintenanceSchedule($id);
    public function getAssetStatistics($id);
    public function scheduleNextMaintenance($id);
} 