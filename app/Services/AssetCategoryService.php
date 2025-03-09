<?php

namespace App\Services;

use App\Models\AssetCategory;
use App\Repositories\Contracts\AssetCategoryRepositoryInterface;
use App\Services\Contracts\AssetCategoryServiceInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class AssetCategoryService extends BaseService implements AssetCategoryServiceInterface
{
    protected $repository;

    public function __construct(AssetCategoryRepositoryInterface $repository)
    {
        parent::__construct();
        $this->repository = $repository;
    }

    public function getValidationRules(string $action = 'create'): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:asset_categories,id',
            'status' => 'required|in:active,inactive',
            'metadata' => 'nullable|array'
        ];

        if ($action === 'update') {
            $rules['id'] = 'required|exists:asset_categories,id';
        }

        return $rules;
    }

    public function getAllCategories(): Collection
    {
        return $this->repository->all();
    }

    public function getPaginatedCategories(int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginate($perPage);
    }

    public function getCategoryById(int $id): ?AssetCategory
    {
        return $this->repository->find($id);
    }

    public function createCategory(array $data): AssetCategory
    {
        $this->validate($data, $this->getValidationRules());
        return $this->repository->create($data);
    }

    public function updateCategory(int $id, array $data): ?AssetCategory
    {
        $data['id'] = $id;
        $this->validate($data, $this->getValidationRules('update'));
        return $this->repository->update($id, $data);
    }

    public function deleteCategory(int $id): bool
    {
        return $this->repository->delete($id);
    }

    public function getActiveCategories(): Collection
    {
        return $this->repository->findByField('status', 'active');
    }

    public function getCategoryHierarchy(): Collection
    {
        return $this->repository->getTree();
    }

    public function moveCategory(int $id, ?int $parentId): bool
    {
        $category = $this->getCategoryById($id);
        if (!$category) {
            return false;
        }

        return $this->repository->move($category, $parentId);
    }

    public function getChildCategories(int $parentId): Collection
    {
        return $this->repository->findByField('parent_id', $parentId);
    }

    public function updateCategoryStatus(int $id, string $status): bool
    {
        $this->validate(['status' => $status], ['status' => 'required|in:active,inactive']);
        return $this->repository->update($id, ['status' => $status]);
    }

    public function searchCategories(string $term): Collection
    {
        return $this->repository->search($term);
    }
} 