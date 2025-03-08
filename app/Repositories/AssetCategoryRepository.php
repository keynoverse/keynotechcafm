<?php

namespace App\Repositories;

use App\Models\AssetCategory;
use App\Repositories\Contracts\AssetCategoryRepositoryInterface;

class AssetCategoryRepository extends BaseRepository implements AssetCategoryRepositoryInterface
{
    public function __construct(AssetCategory $model)
    {
        parent::__construct($model);
    }

    public function findByCode(string $code)
    {
        return $this->model->where('code', $code)->first();
    }

    public function getRootCategories()
    {
        return $this->model->whereNull('parent_id')->get();
    }

    public function getChildCategories($parentId)
    {
        return $this->model->where('parent_id', $parentId)->get();
    }

    public function getCategoryWithAssets($id)
    {
        return $this->model->with('assets')->findOrFail($id);
    }

    public function getCategoryWithDescendants($id)
    {
        $category = $this->model->findOrFail($id);
        return $category->load('descendants');
    }

    public function getAllAssetsInCategory($id)
    {
        $category = $this->model->findOrFail($id);
        return $category->allAssets()->get();
    }
} 