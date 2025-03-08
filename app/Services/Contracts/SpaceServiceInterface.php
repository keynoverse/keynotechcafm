<?php

namespace App\Services\Contracts;

interface SpaceServiceInterface extends BaseServiceInterface
{
    public function findByCode(string $code);
    public function getActiveSpaces();
    public function getSpacesByFloor($floorId);
    public function getSpacesByBuilding($buildingId);
    public function updateStatus($id, string $status);
    public function getAssetCount($id);
    public function getWorkOrderCount($id);
    public function getActiveWorkOrders($id);
    public function getMaintenanceStatistics($id);
    public function getSpaceUtilization($id);
    public function getSpaceWithAssets($id);
    public function getSpaceWithWorkOrders($id);
} 