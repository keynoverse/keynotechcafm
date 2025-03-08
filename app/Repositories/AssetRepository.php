<?php

namespace App\Repositories;

use App\Models\Asset;
use App\Repositories\Contracts\AssetRepositoryInterface;
use Carbon\Carbon;

class AssetRepository extends BaseRepository implements AssetRepositoryInterface
{
    public function __construct(Asset $model)
    {
        parent::__construct($model);
    }

    public function findByAssetNumber(string $assetNumber)
    {
        return $this->model->where('asset_number', $assetNumber)->first();
    }

    public function findByQrCode(string $qrCode)
    {
        return $this->model->where('qr_code', $qrCode)->first();
    }

    public function getActiveAssets()
    {
        return $this->model->where('status', 'active')->get();
    }

    public function getAssetsByCategory($categoryId)
    {
        return $this->model->where('category_id', $categoryId)->get();
    }

    public function getAssetsBySpace($spaceId)
    {
        return $this->model->where('space_id', $spaceId)->get();
    }

    public function getAssetWithMaintenanceSchedules($id)
    {
        return $this->model->with('maintenanceSchedules')->findOrFail($id);
    }

    public function getAssetWithMaintenanceLogs($id)
    {
        return $this->model->with('maintenanceLogs')->findOrFail($id);
    }

    public function getAssetWithWorkOrders($id)
    {
        return $this->model->with('workOrders')->findOrFail($id);
    }

    public function getAssetsNearingWarrantyExpiry($days = 30)
    {
        $futureDate = Carbon::now()->addDays($days);
        return $this->model->whereNotNull('warranty_expiry')
            ->whereDate('warranty_expiry', '<=', $futureDate)
            ->whereDate('warranty_expiry', '>=', Carbon::now())
            ->get();
    }
} 