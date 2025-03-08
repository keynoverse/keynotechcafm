<?php

namespace App\Services;

use App\Repositories\Contracts\SpaceRepositoryInterface;
use App\Services\Contracts\SpaceServiceInterface;
use Illuminate\Support\Facades\DB;
use Exception;

class SpaceService extends BaseService implements SpaceServiceInterface
{
    protected $spaceRepository;

    protected $validationRules = [
        'floor_id' => 'required|exists:floors,id',
        'name' => 'required|string|max:255',
        'code' => 'nullable|string|max:50|unique:spaces,code',
        'type' => 'required|string|in:office,meeting,storage,common,facility',
        'area' => 'nullable|numeric|min:0',
        'capacity' => 'nullable|integer|min:0',
        'status' => 'required|string|in:active,inactive,maintenance,occupied,vacant',
        'metadata' => 'nullable|json'
    ];

    protected $validationMessages = [
        'floor_id.required' => 'Floor ID is required.',
        'floor_id.exists' => 'Selected floor does not exist.',
        'name.required' => 'Space name is required.',
        'type.required' => 'Space type is required.',
        'type.in' => 'Invalid space type provided.',
        'status.in' => 'Invalid space status provided.'
    ];

    public function __construct(SpaceRepositoryInterface $spaceRepository)
    {
        parent::__construct($spaceRepository);
        $this->spaceRepository = $spaceRepository;
    }

    public function findByCode(string $code)
    {
        try {
            $space = $this->spaceRepository->findByCode($code);
            if (!$space) {
                throw new Exception("Space not found with code: {$code}");
            }
            return $space;
        } catch (Exception $e) {
            throw new Exception('Error finding space: ' . $e->getMessage());
        }
    }

    public function getActiveSpaces()
    {
        try {
            return $this->spaceRepository->getActiveSpaces();
        } catch (Exception $e) {
            throw new Exception('Error retrieving active spaces: ' . $e->getMessage());
        }
    }

    public function getSpacesByFloor($floorId)
    {
        try {
            return $this->spaceRepository->getSpacesByFloor($floorId);
        } catch (Exception $e) {
            throw new Exception('Error retrieving spaces by floor: ' . $e->getMessage());
        }
    }

    public function getSpacesByBuilding($buildingId)
    {
        try {
            return $this->spaceRepository->getSpacesByBuilding($buildingId);
        } catch (Exception $e) {
            throw new Exception('Error retrieving spaces by building: ' . $e->getMessage());
        }
    }

    public function updateStatus($id, string $status)
    {
        try {
            $this->validate(
                ['status' => $status],
                ['status' => 'required|string|in:active,inactive,maintenance,occupied,vacant']
            );
            return $this->spaceRepository->update($id, ['status' => $status]);
        } catch (Exception $e) {
            throw new Exception('Error updating space status: ' . $e->getMessage());
        }
    }

    public function getAssetCount($id)
    {
        try {
            return DB::table('assets')
                ->where('space_id', $id)
                ->count();
        } catch (Exception $e) {
            throw new Exception('Error getting asset count: ' . $e->getMessage());
        }
    }

    public function getWorkOrderCount($id)
    {
        try {
            return DB::table('work_orders')
                ->where('space_id', $id)
                ->count();
        } catch (Exception $e) {
            throw new Exception('Error getting work order count: ' . $e->getMessage());
        }
    }

    public function getActiveWorkOrders($id)
    {
        try {
            return DB::table('work_orders')
                ->where('space_id', $id)
                ->where('status', 'active')
                ->get();
        } catch (Exception $e) {
            throw new Exception('Error getting active work orders: ' . $e->getMessage());
        }
    }

    public function getMaintenanceStatistics($id)
    {
        try {
            return DB::table('maintenance_logs')
                ->join('assets', 'maintenance_logs.asset_id', '=', 'assets.id')
                ->where('assets.space_id', $id)
                ->select(
                    DB::raw('COUNT(*) as total_maintenance'),
                    DB::raw('SUM(cost) as total_cost'),
                    DB::raw('AVG(cost) as average_cost')
                )
                ->first();
        } catch (Exception $e) {
            throw new Exception('Error getting maintenance statistics: ' . $e->getMessage());
        }
    }

    public function getSpaceUtilization($id)
    {
        try {
            $space = $this->spaceRepository->find($id);
            if (!$space->capacity) {
                return 0;
            }

            $assetCount = $this->getAssetCount($id);
            return ($assetCount / $space->capacity) * 100;
        } catch (Exception $e) {
            throw new Exception('Error calculating space utilization: ' . $e->getMessage());
        }
    }

    public function getSpaceWithAssets($id)
    {
        try {
            return $this->spaceRepository->getSpaceWithAssets($id);
        } catch (Exception $e) {
            throw new Exception('Error retrieving space with assets: ' . $e->getMessage());
        }
    }

    public function getSpaceWithWorkOrders($id)
    {
        try {
            return $this->spaceRepository->getSpaceWithWorkOrders($id);
        } catch (Exception $e) {
            throw new Exception('Error retrieving space with work orders: ' . $e->getMessage());
        }
    }

    public function create(array $data)
    {
        try {
            if (!isset($data['code'])) {
                $data['code'] = $this->generateUniqueCode($data['floor_id'], $data['name']);
            }
            
            if (!isset($data['status'])) {
                $data['status'] = 'vacant';
            }

            return parent::create($data);
        } catch (Exception $e) {
            throw new Exception('Error creating space: ' . $e->getMessage());
        }
    }

    protected function generateUniqueCode($floorId, $name)
    {
        try {
            $floor = DB::table('floors')->where('id', $floorId)->first();
            $baseCode = strtoupper(substr($floor->code, 0, 5) . '-' . substr(preg_replace('/[^a-zA-Z0-9]/', '', $name), 0, 3));
            $code = $baseCode;
            $counter = 1;

            while ($this->spaceRepository->findByCode($code)) {
                $code = $baseCode . str_pad($counter, 2, '0', STR_PAD_LEFT);
                $counter++;
            }

            return $code;
        } catch (Exception $e) {
            throw new Exception('Error generating unique code: ' . $e->getMessage());
        }
    }

    protected function getUpdateValidationRules($id): array
    {
        $rules = $this->validationRules;
        $rules['code'] = "nullable|string|max:50|unique:spaces,code,{$id}";
        return $rules;
    }
} 