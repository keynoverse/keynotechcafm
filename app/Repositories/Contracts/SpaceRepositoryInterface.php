<?php

namespace App\Repositories\Contracts;

interface SpaceRepositoryInterface extends BaseRepositoryInterface
{
    public function findByCode(string $code);
    public function getActiveSpaces();
    public function getSpacesByFloor($floorId);
    public function getSpacesByBuilding($buildingId);
    public function getSpaceWithAssets($id);
    public function getSpaceWithWorkOrders($id);
} 