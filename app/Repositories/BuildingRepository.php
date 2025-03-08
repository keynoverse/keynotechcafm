<?php

namespace App\Repositories;

use App\Models\Building;
use App\Repositories\Contracts\BuildingRepositoryInterface;

class BuildingRepository extends BaseRepository implements BuildingRepositoryInterface
{
    public function __construct(Building $model)
    {
        parent::__construct($model);
    }

    public function findByCode(string $code)
    {
        return $this->model->where('code', $code)->first();
    }

    public function getActiveBuildings()
    {
        return $this->model->where('status', 'active')->get();
    }

    public function getBuildingsWithFloors()
    {
        return $this->model->with('floors')->get();
    }

    public function getBuildingWithSpaces($id)
    {
        return $this->model->with(['floors.spaces'])->findOrFail($id);
    }
} 