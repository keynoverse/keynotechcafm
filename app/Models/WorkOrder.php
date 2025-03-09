<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 *     schema="WorkOrder",
 *     required={"id", "asset_id", "title", "description", "type", "priority", "status", "requested_by", "due_date", "estimated_hours"},
 *     @OA\Property(property="id", type="integer", format="int64", example=1),
 *     @OA\Property(property="asset_id", type="integer", format="int64", example=1),
 *     @OA\Property(property="space_id", type="integer", format="int64", nullable=true, example=1),
 *     @OA\Property(property="title", type="string", example="AC Unit Not Cooling"),
 *     @OA\Property(property="description", type="string", example="The AC unit in room 101 is not cooling properly"),
 *     @OA\Property(property="type", type="string", enum={"corrective", "preventive", "emergency", "inspection"}, example="corrective"),
 *     @OA\Property(property="priority", type="string", enum={"low", "medium", "high", "critical"}, example="high"),
 *     @OA\Property(property="status", type="string", enum={"pending", "assigned", "in_progress", "on_hold", "completed", "cancelled"}, example="pending"),
 *     @OA\Property(property="requested_by", type="integer", format="int64", example=1),
 *     @OA\Property(property="assigned_to", type="integer", format="int64", nullable=true, example=2),
 *     @OA\Property(property="due_date", type="string", format="date-time", example="2024-03-20 17:00:00"),
 *     @OA\Property(property="estimated_hours", type="number", format="float", example=2.5),
 *     @OA\Property(property="actual_hours", type="number", format="float", nullable=true, example=3.0),
 *     @OA\Property(property="completion_date", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="completion_notes", type="string", nullable=true),
 *     @OA\Property(property="cost", type="number", format="float", nullable=true, example=150.75),
 *     @OA\Property(property="parts_used", type="array", @OA\Items(type="object"), nullable=true),
 *     @OA\Property(property="metadata", type="object", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true)
 * )
 */
class WorkOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'asset_id',
        'space_id',
        'title',
        'description',
        'type',
        'priority',
        'status',
        'requested_by',
        'assigned_to',
        'due_date',
        'estimated_hours',
        'actual_hours',
        'completion_date',
        'completion_notes',
        'cost',
        'parts_used',
        'metadata'
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'completion_date' => 'datetime',
        'estimated_hours' => 'float',
        'actual_hours' => 'float',
        'cost' => 'float',
        'parts_used' => 'array',
        'metadata' => 'array'
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function space(): BelongsTo
    {
        return $this->belongsTo(Space::class);
    }

    public function requestor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(WorkOrderComment::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(WorkOrderAttachment::class);
    }
} 