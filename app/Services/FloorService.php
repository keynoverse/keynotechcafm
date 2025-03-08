<?php

namespace App\Services;

use App\Repositories\Contracts\FloorRepositoryInterface;
use App\Services\Contracts\FloorServiceInterface;
use Illuminate\Support\Facades\DB;
use Exception;

class FloorService extends BaseService implements FloorServiceInterface
{
    protected $floorRepository;

    protected $validationRules = [
        'building_id' => 'required|exists:buildings,id',
        'name' => 'required|string|max:255',
        'number' => 'required|integer',
        'code' => 'nullable|string|max:50|unique:floors,code',
        'status' => 'required|string|in:active,inactive,maintenance',
        'metadata' => 'nullable|json'
    ];

    protected $validationMessages = [
        'building_id.required' => 'Building ID is required.',
        'building_id.exists' => 'Selected building does not exist.',
        'name.required' => 'Floor name is required.',
        'number.required' => 'Floor number is required.',
        'status.in' => 'Invalid floor status provided.'
    ];

    public function __construct(FloorRepositoryInterface $floorRepository)
    {
        parent::__construct($floorRepository);
        $this->floorRepository = $floorRepository;
    }

    public function findByCode(string $code)
    {
        try {
            $floor = $this->floorRepository->findByCode($code);
            if (!$floor) {
                throw new Exception("Floor not found with code: {$code}");
            }
            return $floor;
        } catch (Exception $e) {
            throw new Exception('Error finding floor: ' . $e->getMessage());
        }
    }

    public function getActiveFloors()
    {
        try {
            return $this->floorRepository->getActiveFloors();
        } catch (Exception $e) {
            throw new Exception('Error retrieving active floors: ' . $e->getMessage());
        }
    }

    public function getFloorsByBuilding($buildingId)
    {
        try {
            return $this->floorRepository->getFloorsByBuilding($buildingId);
        } catch (Exception $e) {
            throw new Exception('Error retrieving floors by building: ' . $e->getMessage());
        }
    }

    public function getFloorWithSpaces($id)
    {
        try {
            return $this->floorRepository->getFloorWithSpaces($id);
        } catch (Exception $e) {
            throw new Exception('Error retrieving floor with spaces: ' . $e->getMessage());
        }
    }

    public function updateStatus($id, string $status)
    {
        try {
            $this->validate(['status' => $status], ['status' => 'required|string|in:active,inactive,maintenance']);
            return $this->floorRepository->update($id, ['status' => $status]);
        } catch (Exception $e) {
            throw new Exception('Error updating floor status: ' . $e->getMessage());
        }
    }

    public function getSpaceCount($id)
    {
        try {
            $floor = $this->floorRepository->getFloorWithSpaces($id);
            return $floor->spaces->count();
        } catch (Exception $e) {
            throw new Exception('Error getting space count: ' . $e->getMessage());
        }
    }

    public function getAssetCount($id)
    {
        try {
            return DB::table('assets')
                ->join('spaces', 'assets.space_id', '=', 'spaces.id')
                ->where('spaces.floor_id', $id)
                ->count();
        } catch (Exception $e) {
            throw new Exception('Error getting asset count: ' . $e->getMessage());
        }
    }

    public function getOccupancyRate($id)
    {
        try {
            $floor = $this->floorRepository->getFloorWithSpaces($id);
            $totalSpaces = $floor->spaces->count();
            $occupiedSpaces = $floor->spaces->where('status', 'occupied')->count();
            
            return $totalSpaces > 0 ? ($occupiedSpaces / $totalSpaces) * 100 : 0;
        } catch (Exception $e) {
            throw new Exception('Error calculating occupancy rate: ' . $e->getMessage());
        }
    }

    public function getMaintenanceStatistics($id)
    {
        try {
            return DB::table('maintenance_logs')
                ->join('assets', 'maintenance_logs.asset_id', '=', 'assets.id')
                ->join('spaces', 'assets.space_id', '=', 'spaces.id')
                ->where('spaces.floor_id', $id)
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

    public function create(array $data)
    {
        try {
            if (!isset($data['code'])) {
                $data['code'] = $this->generateUniqueCode($data['building_id'], $data['number']);
            }
            
            if (!isset($data['status'])) {
                $data['status'] = 'active';
            }

            return parent::create($data);
        } catch (Exception $e) {
            throw new Exception('Error creating floor: ' . $e->getMessage());
        }
    }

    protected function generateUniqueCode($buildingId, $floorNumber)
    {
        try {
            $building = DB::table('buildings')->where('id', $buildingId)->first();
            $baseCode = strtoupper(substr($building->code, 0, 3) . 'F' . $floorNumber);
            $code = $baseCode;
            $counter = 1;

            while ($this->floorRepository->findByCode($code)) {
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
        $rules['code'] = "nullable|string|max:50|unique:floors,code,{$id}";
        return $rules;
    }
} 