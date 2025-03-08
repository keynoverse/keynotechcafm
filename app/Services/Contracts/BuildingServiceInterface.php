<?php

namespace App\Services\Contracts;

interface BuildingServiceInterface extends BaseServiceInterface
{
    public function findByCode(string $code);
    public function getActiveBuildings();
    public function getBuildingsWithFloors();
    public function getBuildingWithSpaces($id);
    public function updateStatus($id, string $status);
    public function getSpaceCount($id);
    public function getAssetCount($id);
    public function getActiveWorkOrders($id);
} 