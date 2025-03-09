<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 *     schema="MaintenanceSchedule",
 *     required={"id", "asset_id", "title", "scheduled_date", "frequency", "frequency_unit", "priority", "status"},
 *     @OA\Property(property="id", type="integer", format="int64", example=1),
 *     @OA\Property(property="asset_id", type="integer", format="int64", example=1),
 *     @OA\Property(property="title", type="string", example="Monthly AC Maintenance"),
 *     @OA\Property(property="description", type="string", nullable=true, example="Regular maintenance check of the air conditioning unit"),
 *     @OA\Property(property="scheduled_date", type="string", format="date-time", example="2024-03-15 10:00:00"),
 *     @OA\Property(property="frequency", type="integer", example=30),
 *     @OA\Property(property="frequency_unit", type="string", enum={"days", "weeks", "months", "years"}, example="days"),
 *     @OA\Property(property="priority", type="string", enum={"low", "medium", "high"}, example="medium"),
 *     @OA\Property(property="status", type="string", enum={"scheduled", "completed", "cancelled", "overdue"}, example="scheduled"),
 *     @OA\Property(property="assigned_to", type="integer", format="int64", nullable=true, example=1),
 *     @OA\Property(property="completion_date", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="completion_notes", type="string", nullable=true),
 *     @OA\Property(property="completed_by", type="integer", format="int64", nullable=true),
 *     @OA\Property(property="notes", type="string", nullable=true),
 *     @OA\Property(property="metadata", type="object", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true)
 * )
 */
class MaintenanceSchedule extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'asset_id',
        'title',
        'description',
        'scheduled_date',
        'frequency',
        'frequency_unit',
        'priority',
        'status',
        'assigned_to',
        'completion_date',
        'completion_notes',
        'completed_by',
        'notes',
        'metadata'
    ];

    protected $casts = [
        'scheduled_date' => 'datetime',
        'completion_date' => 'datetime',
        'metadata' => 'array'
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function maintenanceLogs()
    {
        return $this->hasMany(MaintenanceLog::class, 'schedule_id');
    }
} 