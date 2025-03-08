<?php

namespace App\Repositories\Contracts;

interface WorkOrderRepositoryInterface extends BaseRepositoryInterface
{
    public function findByNumber(string $number);
    public function getWorkOrdersByStatus($status);
    public function getWorkOrdersByPriority($priority);
    public function getWorkOrdersByAsset($assetId);
    public function getWorkOrdersBySpace($spaceId);
    public function getWorkOrdersByRequestor($userId);
    public function getWorkOrdersByAssignee($userId);
    public function getOverdueWorkOrders();
    public function getDueWorkOrders($days = 7);
    public function getWorkOrderWithComments($id);
    public function getWorkOrderWithAttachments($id);
    public function getWorkOrderWithAll($id);
} 