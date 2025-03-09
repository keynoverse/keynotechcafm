<?php

namespace App\Services\Contracts;

use App\Models\AssetCategory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface AssetCategoryServiceInterface
{
    public function getAllCategories(): Collection;
    
    public function getPaginatedCategories(int $perPage = 15): LengthAwarePaginator;
    
    public function getCategoryById(int $id): ?AssetCategory;
    
    public function createCategory(array $data): AssetCategory;
    
    public function updateCategory(int $id, array $data): ?AssetCategory;
    
    public function deleteCategory(int $id): bool;
    
    public function getActiveCategories(): Collection;
    
    public function getCategoryHierarchy(): Collection;
    
    public function moveCategory(int $id, ?int $parentId): bool;
    
    public function getChildCategories(int $parentId): Collection;
    
    public function updateCategoryStatus(int $id, string $status): bool;
    
    public function searchCategories(string $term): Collection;
} 