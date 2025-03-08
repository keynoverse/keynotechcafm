<?php

namespace App\Services;

use App\Repositories\Contracts\AssetRepositoryInterface;
use App\Services\Contracts\AssetServiceInterface;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

class AssetService extends BaseService implements AssetServiceInterface
{
    protected $assetRepository;

    protected $validationRules = [
        'category_id' => 'required|exists:asset_categories,id',
        'space_id' => 'required|exists:spaces,id',
        'name' => 'required|string|max:255',
        'code' => 'nullable|string|max:50|unique:assets,code',
        'description' => 'nullable|string',
        'model' => 'nullable|string|max:100',
        'manufacturer' => 'nullable|string|max:100',
        'serial_number' => 'nullable|string|max:100|unique:assets,serial_number',
        'purchase_date' => 'nullable|date',
        'purchase_cost' => 'nullable|numeric|min:0',
        'warranty_expiry' => 'nullable|date|after_or_equal:purchase_date',
        'maintenance_frequency' => 'nullable|integer|min:1',
        'maintenance_unit' => 'nullable|string|in:days,weeks,months,years',
        'next_maintenance_date' => 'nullable|date|after:today',
        'status' => 'required|string|in:active,inactive,maintenance,retired,storage',
        'condition' => 'required|string|in:excellent,good,fair,poor',
        'criticality' => 'required|string|in:high,medium,low',
        'metadata' => 'nullable|json'
    ];

    protected $validationMessages = [
        'category_id.required' => 'Asset category is required.',
        'category_id.exists' => 'Selected asset category does not exist.',
        'space_id.required' => 'Space assignment is required.',
        'space_id.exists' => 'Selected space does not exist.',
        'name.required' => 'Asset name is required.',
        'serial_number.unique' => 'Serial number must be unique.',
        'warranty_expiry.after_or_equal' => 'Warranty expiry date must be after or equal to purchase date.',
        'next_maintenance_date.after' => 'Next maintenance date must be a future date.'
    ];

    public function __construct(AssetRepositoryInterface $assetRepository)
    {
        parent::__construct($assetRepository);
        $this->assetRepository = $assetRepository;
    }

    public function findByCode(string $code)
    {
        try {
            $asset = $this->assetRepository->findByCode($code);
            if (!$asset) {
                throw new Exception("Asset not found with code: {$code}");
            }
            return $asset;
        } catch (Exception $e) {
            throw new Exception('Error finding asset: ' . $e->getMessage());
        }
    }

    public function getActiveAssets()
    {
        try {
            return $this->assetRepository->getActiveAssets();
        } catch (Exception $e) {
            throw new Exception('Error retrieving active assets: ' . $e->getMessage());
        }
    }

    public function getAssetsBySpace($spaceId)
    {
        try {
            return $this->assetRepository->getAssetsBySpace($spaceId);
        } catch (Exception $e) {
            throw new Exception('Error retrieving assets by space: ' . $e->getMessage());
        }
    }

    public function getAssetsByFloor($floorId)
    {
        try {
            return $this->assetRepository->getAssetsByFloor($floorId);
        } catch (Exception $e) {
            throw new Exception('Error retrieving assets by floor: ' . $e->getMessage());
        }
    }

    public function getAssetsByBuilding($buildingId)
    {
        try {
            return $this->assetRepository->getAssetsByBuilding($buildingId);
        } catch (Exception $e) {
            throw new Exception('Error retrieving assets by building: ' . $e->getMessage());
        }
    }

    public function getAssetsByCategory($categoryId)
    {
        try {
            return $this->assetRepository->getAssetsByCategory($categoryId);
        } catch (Exception $e) {
            throw new Exception('Error retrieving assets by category: ' . $e->getMessage());
        }
    }

    public function updateStatus($id, string $status)
    {
        try {
            $this->validate(
                ['status' => $status],
                ['status' => 'required|string|in:active,inactive,maintenance,retired,storage']
            );

            $asset = $this->assetRepository->update($id, ['status' => $status]);

            // If status is changed to maintenance, schedule next maintenance
            if ($status === 'maintenance') {
                $this->scheduleNextMaintenance($id);
            }

            return $asset;
        } catch (Exception $e) {
            throw new Exception('Error updating asset status: ' . $e->getMessage());
        }
    }

    public function assignToSpace($id, $spaceId)
    {
        try {
            $this->validate(
                ['space_id' => $spaceId],
                ['space_id' => 'required|exists:spaces,id']
            );

            return $this->assetRepository->update($id, ['space_id' => $spaceId]);
        } catch (Exception $e) {
            throw new Exception('Error assigning asset to space: ' . $e->getMessage());
        }
    }

    public function getMaintenanceHistory($id)
    {
        try {
            return DB::table('maintenance_logs')
                ->where('asset_id', $id)
                ->orderBy('maintenance_date', 'desc')
                ->get();
        } catch (Exception $e) {
            throw new Exception('Error retrieving maintenance history: ' . $e->getMessage());
        }
    }

    public function getWorkOrderHistory($id)
    {
        try {
            return DB::table('work_orders')
                ->where('asset_id', $id)
                ->orderBy('created_at', 'desc')
                ->get();
        } catch (Exception $e) {
            throw new Exception('Error retrieving work order history: ' . $e->getMessage());
        }
    }

    public function getAssetUtilization($id)
    {
        try {
            $totalHours = 0;
            $maintenanceHours = 0;

            $maintenanceLogs = DB::table('maintenance_logs')
                ->where('asset_id', $id)
                ->whereYear('maintenance_date', Carbon::now()->year)
                ->get();

            foreach ($maintenanceLogs as $log) {
                $maintenanceHours += $log->duration ?? 0;
            }

            $totalHours = Carbon::now()->startOfYear()->diffInHours(Carbon::now());
            
            return [
                'utilization_rate' => ($totalHours - $maintenanceHours) / $totalHours * 100,
                'maintenance_hours' => $maintenanceHours,
                'total_hours' => $totalHours
            ];
        } catch (Exception $e) {
            throw new Exception('Error calculating asset utilization: ' . $e->getMessage());
        }
    }

    public function getAssetWithMaintenanceSchedule($id)
    {
        try {
            return $this->assetRepository->getAssetWithMaintenanceSchedule($id);
        } catch (Exception $e) {
            throw new Exception('Error retrieving asset with maintenance schedule: ' . $e->getMessage());
        }
    }

    public function getAssetStatistics($id)
    {
        try {
            $maintenanceStats = DB::table('maintenance_logs')
                ->where('asset_id', $id)
                ->select(
                    DB::raw('COUNT(*) as total_maintenance'),
                    DB::raw('SUM(cost) as total_cost'),
                    DB::raw('AVG(cost) as average_cost'),
                    DB::raw('AVG(duration) as average_duration')
                )
                ->first();

            $workOrderStats = DB::table('work_orders')
                ->where('asset_id', $id)
                ->select(
                    DB::raw('COUNT(*) as total_work_orders'),
                    DB::raw('COUNT(CASE WHEN status = "completed" THEN 1 END) as completed_work_orders'),
                    DB::raw('COUNT(CASE WHEN priority = "high" THEN 1 END) as high_priority_work_orders')
                )
                ->first();

            return [
                'maintenance_statistics' => $maintenanceStats,
                'work_order_statistics' => $workOrderStats,
                'utilization' => $this->getAssetUtilization($id)
            ];
        } catch (Exception $e) {
            throw new Exception('Error retrieving asset statistics: ' . $e->getMessage());
        }
    }

    public function scheduleNextMaintenance($id)
    {
        try {
            $asset = $this->find($id);
            
            if (!$asset->maintenance_frequency || !$asset->maintenance_unit) {
                return null;
            }

            $lastMaintenance = DB::table('maintenance_logs')
                ->where('asset_id', $id)
                ->orderBy('maintenance_date', 'desc')
                ->first();

            $baseDate = $lastMaintenance ? Carbon::parse($lastMaintenance->maintenance_date) : Carbon::now();
            
            $nextMaintenanceDate = $baseDate->add(
                $asset->maintenance_frequency,
                $asset->maintenance_unit
            );

            return $this->update($id, [
                'next_maintenance_date' => $nextMaintenanceDate
            ]);
        } catch (Exception $e) {
            throw new Exception('Error scheduling next maintenance: ' . $e->getMessage());
        }
    }

    public function create(array $data)
    {
        try {
            if (!isset($data['code'])) {
                $data['code'] = $this->generateUniqueCode($data);
            }

            if (!isset($data['status'])) {
                $data['status'] = 'active';
            }

            $asset = parent::create($data);

            if (isset($data['maintenance_frequency']) && isset($data['maintenance_unit'])) {
                $this->scheduleNextMaintenance($asset->id);
            }

            return $asset;
        } catch (Exception $e) {
            throw new Exception('Error creating asset: ' . $e->getMessage());
        }
    }

    protected function generateUniqueCode($data)
    {
        try {
            $category = DB::table('asset_categories')->where('id', $data['category_id'])->first();
            $space = DB::table('spaces')->where('id', $data['space_id'])->first();
            
            $baseCode = strtoupper(
                substr($category->code ?? 'AST', 0, 3) . '-' . 
                substr($space->code ?? 'SP', 0, 3) . '-' .
                substr(preg_replace('/[^a-zA-Z0-9]/', '', $data['name']), 0, 3)
            );
            
            $code = $baseCode;
            $counter = 1;

            while ($this->assetRepository->findByCode($code)) {
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
        $rules['code'] = "nullable|string|max:50|unique:assets,code,{$id}";
        $rules['serial_number'] = "nullable|string|max:100|unique:assets,serial_number,{$id}";
        return $rules;
    }
} 