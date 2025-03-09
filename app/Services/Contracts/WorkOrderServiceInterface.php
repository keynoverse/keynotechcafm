<?php

namespace App\Services\Contracts;

use App\Models\WorkOrder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface WorkOrderServiceInterface
{
    public function getAllWorkOrders(): Collection;
    
    public function getPaginatedWorkOrders(int $perPage = 15): LengthAwarePaginator;
    
    public function getWorkOrderById(int $id): ?WorkOrder;
    
    public function createWorkOrder(array $data): WorkOrder;
    
    public function updateWorkOrder(int $id, array $data): ?WorkOrder;
    
    public function deleteWorkOrder(int $id): bool;
    
    public function getAssetWorkOrders(int $assetId): Collection;
    
    public function getSpaceWorkOrders(int $spaceId): Collection;
    
    public function getWorkOrdersByDateRange(string $startDate, string $endDate): Collection;
    
    public function getWorkOrdersByType(string $type): Collection;
    
    public function getWorkOrdersByPriority(string $priority): Collection;
    
    public function getWorkOrdersByStatus(string $status): Collection;
    
    public function getWorkOrdersByAssignee(int $assigneeId): Collection;
    
    public function getWorkOrdersByRequestor(int $requestorId): Collection;
    
    public function addComment(int $workOrderId, array $commentData): bool;
    
    public function removeComment(int $workOrderId, int $commentId): bool;
    
    public function addAttachment(int $workOrderId, array $attachmentData): bool;
    
    public function removeAttachment(int $workOrderId, int $attachmentId): bool;
    
    public function updateWorkOrderStatus(int $id, string $status): bool;
    
    public function assignWorkOrder(int $id, int $assigneeId): bool;
    
    public function getWorkOrderStatistics(): array;
} 