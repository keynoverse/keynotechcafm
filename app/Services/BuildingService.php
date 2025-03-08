<?php

namespace App\Services;

use App\Repositories\Contracts\BuildingRepositoryInterface;
use App\Services\Contracts\BuildingServiceInterface;
use Illuminate\Support\Facades\DB;
use Exception;

class BuildingService extends BaseService implements BuildingServiceInterface
{
    protected $buildingRepository;

    protected $validationRules = [
        'name' => 'required|string|max:255',
        'code' => 'nullable|string|max:50|unique:buildings,code',
        'address' => 'required|string|max:255',
        'city' => 'required|string|max:100',
        'state' => 'required|string|max:100',
        'country' => 'required|string|max:100',
        'postal_code' => 'required|string|max:20',
        'status' => 'required|string|in:active,inactive,maintenance',
        'metadata' => 'nullable|json'
    ];

    protected $validationMessages = [
        'name.required' => 'Building name is required.',
        'address.required' => 'Building address is required.',
        'status.in' => 'Invalid building status provided.'
    ];

    public function __construct(BuildingRepositoryInterface $buildingRepository)
    {
        parent::__construct($buildingRepository);
        $this->buildingRepository = $buildingRepository;
    }

    public function findByCode(string $code)
    {
        try {
            $building = $this->buildingRepository->findByCode($code);
            if (!$building) {
                throw new Exception("Building not found with code: {$code}");
            }
            return $building;
        } catch (Exception $e) {
            throw new Exception('Error finding building: ' . $e->getMessage());
        }
    }

    public function getActiveBuildings()
    {
        try {
            return $this->buildingRepository->getActiveBuildings();
        } catch (Exception $e) {
            throw new Exception('Error retrieving active buildings: ' . $e->getMessage());
        }
    }

    public function getBuildingsWithFloors()
    {
        try {
            return $this->buildingRepository->getBuildingsWithFloors();
        } catch (Exception $e) {
            throw new Exception('Error retrieving buildings with floors: ' . $e->getMessage());
        }
    }

    public function getBuildingWithSpaces($id)
    {
        try {
            return $this->buildingRepository->getBuildingWithSpaces($id);
        } catch (Exception $e) {
            throw new Exception('Error retrieving building with spaces: ' . $e->getMessage());
        }
    }

    public function updateStatus($id, string $status)
    {
        try {
            $this->validate(['status' => $status], ['status' => 'required|string|in:active,inactive,maintenance']);
            return $this->buildingRepository->update($id, ['status' => $status]);
        } catch (Exception $e) {
            throw new Exception('Error updating building status: ' . $e->getMessage());
        }
    }

    public function getSpaceCount($id)
    {
        try {
            $building = $this->buildingRepository->getBuildingWithSpaces($id);
            return $building->spaces->count();
        } catch (Exception $e) {
            throw new Exception('Error getting space count: ' . $e->getMessage());
        }
    }

    public function getAssetCount($id)
    {
        try {
            return DB::table('assets')
                ->join('spaces', 'assets.space_id', '=', 'spaces.id')
                ->join('floors', 'spaces.floor_id', '=', 'floors.id')
                ->where('floors.building_id', $id)
                ->count();
        } catch (Exception $e) {
            throw new Exception('Error getting asset count: ' . $e->getMessage());
        }
    }

    public function getActiveWorkOrders($id)
    {
        try {
            return DB::table('work_orders')
                ->join('spaces', 'work_orders.space_id', '=', 'spaces.id')
                ->join('floors', 'spaces.floor_id', '=', 'floors.id')
                ->where('floors.building_id', $id)
                ->where('work_orders.status', 'active')
                ->get();
        } catch (Exception $e) {
            throw new Exception('Error getting active work orders: ' . $e->getMessage());
        }
    }

    public function create(array $data)
    {
        try {
            if (!isset($data['code'])) {
                $data['code'] = $this->generateUniqueCode($data['name']);
            }
            
            if (!isset($data['status'])) {
                $data['status'] = 'active';
            }

            return parent::create($data);
        } catch (Exception $e) {
            throw new Exception('Error creating building: ' . $e->getMessage());
        }
    }

    public function getMaintenanceStatistics($id)
    {
        try {
            return DB::table('maintenance_logs')
                ->join('assets', 'maintenance_logs.asset_id', '=', 'assets.id')
                ->join('spaces', 'assets.space_id', '=', 'spaces.id')
                ->join('floors', 'spaces.floor_id', '=', 'floors.id')
                ->where('floors.building_id', $id)
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

    public function getOccupancyRate($id)
    {
        try {
            $building = $this->buildingRepository->getBuildingWithSpaces($id);
            $totalSpaces = $building->spaces->count();
            $occupiedSpaces = $building->spaces->where('status', 'occupied')->count();
            
            return $totalSpaces > 0 ? ($occupiedSpaces / $totalSpaces) * 100 : 0;
        } catch (Exception $e) {
            throw new Exception('Error calculating occupancy rate: ' . $e->getMessage());
        }
    }

    protected function generateUniqueCode($name)
    {
        try {
            $baseCode = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $name), 0, 3));
            $code = $baseCode;
            $counter = 1;

            while ($this->buildingRepository->findByCode($code)) {
                $code = $baseCode . str_pad($counter, 3, '0', STR_PAD_LEFT);
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
        $rules['code'] = "nullable|string|max:50|unique:buildings,code,{$id}";
        return $rules;
    }
} 