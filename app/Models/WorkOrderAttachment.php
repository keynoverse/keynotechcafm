<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkOrderAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_order_id',
        'file_name',
        'file_path',
        'file_type',
        'file_size'
    ];

    // Relationships
    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }
} 