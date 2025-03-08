<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Asset extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'asset_number',
        'category_id',
        'status',
        'space_id',
        'purchase_date',
        'warranty_expiry',
        'purchase_cost',
        'manufacturer',
        'model',
        'serial_number',
        'qr_code',
        'specifications',
        'metadata'
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'warranty_expiry' => 'date',
        'purchase_cost' => 'decimal:2',
        'specifications' => 'json',
        'metadata' => 'json'
    ];

    // Relationships
    public function category()
    {
        return $this->belongsTo(AssetCategory::class, 'category_id');
    }

    public function space()
    {
        return $this->belongsTo(Space::class);
    }

    public function maintenanceSchedules()
    {
        return $this->hasMany(MaintenanceSchedule::class);
    }

    public function maintenanceLogs()
    {
        return $this->hasMany(MaintenanceLog::class);
    }

    public function workOrders()
    {
        return $this->hasMany(WorkOrder::class);
    }
} 