<?php

namespace App\Repositories\Contracts;

interface WorkOrderAttachmentRepositoryInterface extends BaseRepositoryInterface
{
    public function getAttachmentsByWorkOrder($workOrderId);
    public function getAttachmentsByType($type);
    public function getRecentAttachments($days = 7);
    public function findByFileName($fileName);
} 