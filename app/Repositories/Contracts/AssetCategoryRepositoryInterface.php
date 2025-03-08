<?php

namespace App\Repositories\Contracts;

interface AssetCategoryRepositoryInterface extends BaseRepositoryInterface
{
    public function findByCode(string $code);
    public function getRootCategories();
    public function getChildCategories($parentId);
    public function getCategoryWithAssets($id);
    public function getCategoryWithDescendants($id);
    public function getAllAssetsInCategory($id);
} 