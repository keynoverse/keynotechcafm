<?php

namespace App\Repositories\Contracts;

interface BuildingRepositoryInterface extends BaseRepositoryInterface
{
    public function findByCode(string $code);
    public function getActiveBuildings();
    public function getBuildingsWithFloors();
    public function getBuildingWithSpaces($id);
} 