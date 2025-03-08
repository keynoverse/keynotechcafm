<?php

namespace App\Repositories;

use App\Models\Space;
use App\Repositories\Contracts\SpaceRepositoryInterface;

class SpaceRepository extends BaseRepository implements SpaceRepositoryInterface
{
    public function __construct(Space $model)
    {
        parent::__construct($model);
    }

    public function findByCode(string $code)
    {
        return $this->model->where('code', $code)->first();
    }

    public function getActiveSpaces()
    {
        return $this->model->where('status', 'active')->get();
    }

    public function getSpacesByFloor($floorId)
    {
        return $this->model->where('floor_id', $floorId)->get();
    }

    public function getSpacesByBuilding($buildingId)
    {
        return $this->model->whereHas('floor', function ($query) use ($buildingId) {
            $query->where('building_id', $buildingId);
        })->get();
    }

    public function getSpaceWithAssets($id)
    {
        return $this->model->with('assets')->findOrFail($id);
    }

    public function getSpaceWithWorkOrders($id)
    {
        return $this->model->with('workOrders')->findOrFail($id);
    }
} 