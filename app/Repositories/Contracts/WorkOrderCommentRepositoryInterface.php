<?php

namespace App\Repositories\Contracts;

interface WorkOrderCommentRepositoryInterface extends BaseRepositoryInterface
{
    public function getCommentsByWorkOrder($workOrderId);
    public function getCommentsByUser($userId);
    public function getRecentComments($days = 7);
    public function getCommentWithUser($id);
} 