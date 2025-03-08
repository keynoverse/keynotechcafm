<?php

namespace App\Repositories\Contracts;

interface FloorRepositoryInterface extends BaseRepositoryInterface
{
    public function findByCode(string $code);
    public function getActiveFloors();
    public function getFloorsByBuilding($buildingId);
    public function getFloorWithSpaces($id);
} 