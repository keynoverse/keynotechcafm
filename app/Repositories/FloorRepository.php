<?php

namespace App\Repositories;

use App\Models\Floor;
use App\Repositories\Contracts\FloorRepositoryInterface;

class FloorRepository extends BaseRepository implements FloorRepositoryInterface
{
    public function __construct(Floor $model)
    {
        parent::__construct($model);
    }

    public function findByCode(string $code)
    {
        return $this->model->where('code', $code)->first();
    }

    public function getActiveFloors()
    {
        return $this->model->where('status', 'active')->get();
    }

    public function getFloorsByBuilding($buildingId)
    {
        return $this->model->where('building_id', $buildingId)->get();
    }

    public function getFloorWithSpaces($id)
    {
        return $this->model->with('spaces')->findOrFail($id);
    }
} 