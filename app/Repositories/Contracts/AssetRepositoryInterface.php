<?php

namespace App\Repositories\Contracts;

interface AssetRepositoryInterface extends BaseRepositoryInterface
{
    public function findByAssetNumber(string $assetNumber);
    public function findByQrCode(string $qrCode);
    public function getActiveAssets();
    public function getAssetsByCategory($categoryId);
    public function getAssetsBySpace($spaceId);
    public function getAssetWithMaintenanceSchedules($id);
    public function getAssetWithMaintenanceLogs($id);
    public function getAssetWithWorkOrders($id);
    public function getAssetsNearingWarrantyExpiry($days = 30);
} 