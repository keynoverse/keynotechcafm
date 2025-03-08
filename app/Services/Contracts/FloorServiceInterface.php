<?php

namespace App\Services\Contracts;

interface FloorServiceInterface extends BaseServiceInterface
{
    public function findByCode(string $code);
    public function getActiveFloors();
    public function getFloorsByBuilding($buildingId);
    public function getFloorWithSpaces($id);
    public function updateStatus($id, string $status);
    public function getSpaceCount($id);
    public function getAssetCount($id);
    public function getOccupancyRate($id);
    public function getMaintenanceStatistics($id);
} 