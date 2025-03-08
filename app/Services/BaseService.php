<?php

namespace App\Services;

use App\Repositories\Contracts\BaseRepositoryInterface;
use App\Services\Contracts\BaseServiceInterface;
use App\Services\Traits\ValidatesData;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

abstract class BaseService implements BaseServiceInterface
{
    use ValidatesData;

    protected $repository;
    protected $validationRules = [];
    protected $validationMessages = [];

    public function __construct(BaseRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function all()
    {
        try {
            return $this->repository->all();
        } catch (Exception $e) {
            throw new Exception('Error retrieving records: ' . $e->getMessage());
        }
    }

    public function find($id)
    {
        try {
            $record = $this->repository->find($id);
            if (!$record) {
                throw new ModelNotFoundException("Record not found with ID: {$id}");
            }
            return $record;
        } catch (Exception $e) {
            throw new Exception('Error finding record: ' . $e->getMessage());
        }
    }

    public function create(array $data)
    {
        try {
            if (!empty($this->validationRules)) {
                $this->validate($data, $this->validationRules, $this->validationMessages);
            }
            return $this->repository->create($data);
        } catch (Exception $e) {
            throw new Exception('Error creating record: ' . $e->getMessage());
        }
    }

    public function update($id, array $data)
    {
        try {
            if (!empty($this->validationRules)) {
                $this->validate($data, $this->getUpdateValidationRules($id), $this->validationMessages);
            }
            $record = $this->repository->update($id, $data);
            if (!$record) {
                throw new ModelNotFoundException("Record not found with ID: {$id}");
            }
            return $record;
        } catch (Exception $e) {
            throw new Exception('Error updating record: ' . $e->getMessage());
        }
    }

    public function delete($id)
    {
        try {
            if (!$this->repository->delete($id)) {
                throw new ModelNotFoundException("Record not found with ID: {$id}");
            }
            return true;
        } catch (Exception $e) {
            throw new Exception('Error deleting record: ' . $e->getMessage());
        }
    }

    public function paginate($perPage = 15)
    {
        try {
            return $this->repository->paginate($perPage);
        } catch (Exception $e) {
            throw new Exception('Error retrieving paginated records: ' . $e->getMessage());
        }
    }

    protected function getUpdateValidationRules($id): array
    {
        return $this->validationRules;
    }
} 