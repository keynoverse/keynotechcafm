<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 *     schema="Asset",
 *     required={"id", "category_id", "space_id", "name", "status", "condition", "criticality"},
 *     @OA\Property(property="id", type="integer", format="int64", example=1),
 *     @OA\Property(property="category_id", type="integer", format="int64", example=1),
 *     @OA\Property(property="space_id", type="integer", format="int64", example=1),
 *     @OA\Property(property="name", type="string", example="Office Chair"),
 *     @OA\Property(property="code", type="string", example="AST-001"),
 *     @OA\Property(property="description", type="string", example="Ergonomic office chair"),
 *     @OA\Property(property="model", type="string", example="XYZ-123"),
 *     @OA\Property(property="manufacturer", type="string", example="ABC Corp"),
 *     @OA\Property(property="serial_number", type="string", example="SN123456"),
 *     @OA\Property(property="purchase_date", type="string", format="date", example="2023-01-01"),
 *     @OA\Property(property="purchase_cost", type="number", format="float", example=299.99),
 *     @OA\Property(property="warranty_expiry", type="string", format="date", example="2024-01-01"),
 *     @OA\Property(property="maintenance_frequency", type="integer", example=30),
 *     @OA\Property(property="maintenance_unit", type="string", enum={"days", "weeks", "months", "years"}, example="days"),
 *     @OA\Property(property="next_maintenance_date", type="string", format="date", example="2023-12-31"),
 *     @OA\Property(property="status", type="string", enum={"active", "inactive", "maintenance", "retired", "storage"}, example="active"),
 *     @OA\Property(property="condition", type="string", enum={"excellent", "good", "fair", "poor"}, example="excellent"),
 *     @OA\Property(property="criticality", type="string", enum={"high", "medium", "low"}, example="medium"),
 *     @OA\Property(property="metadata", type="object"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class Asset extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'space_id',
        'name',
        'code',
        'description',
        'model',
        'manufacturer',
        'serial_number',
        'purchase_date',
        'purchase_cost',
        'warranty_expiry',
        'maintenance_frequency',
        'maintenance_unit',
        'next_maintenance_date',
        'status',
        'condition',
        'criticality',
        'metadata'
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'warranty_expiry' => 'date',
        'next_maintenance_date' => 'date',
        'purchase_cost' => 'float',
        'metadata' => 'array'
    ];

    // Relationships
    public function category(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class, 'category_id');
    }

    public function space(): BelongsTo
    {
        return $this->belongsTo(Space::class, 'space_id');
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