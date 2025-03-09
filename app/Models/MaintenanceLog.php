<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 *     schema="MaintenanceLog",
 *     required={"id", "asset_id", "type", "title", "description", "performed_by", "performed_at", "duration", "duration_unit", "cost", "status"},
 *     @OA\Property(property="id", type="integer", format="int64", example=1),
 *     @OA\Property(property="asset_id", type="integer", format="int64", example=1),
 *     @OA\Property(property="schedule_id", type="integer", format="int64", nullable=true, example=1),
 *     @OA\Property(property="type", type="string", enum={"preventive", "corrective", "emergency"}, example="preventive"),
 *     @OA\Property(property="title", type="string", example="Monthly AC Filter Cleaning"),
 *     @OA\Property(property="description", type="string", example="Cleaned and replaced AC filters as per maintenance schedule"),
 *     @OA\Property(property="performed_by", type="integer", format="int64", example=1),
 *     @OA\Property(property="performed_at", type="string", format="date-time", example="2024-03-15 10:00:00"),
 *     @OA\Property(property="duration", type="integer", example=30),
 *     @OA\Property(property="duration_unit", type="string", enum={"minutes", "hours"}, example="minutes"),
 *     @OA\Property(property="cost", type="number", format="float", example=150.50),
 *     @OA\Property(property="parts_used", type="array", @OA\Items(type="object"), nullable=true),
 *     @OA\Property(property="status", type="string", enum={"pending", "in_progress", "completed", "cancelled"}, example="completed"),
 *     @OA\Property(property="notes", type="string", nullable=true),
 *     @OA\Property(property="metadata", type="object", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true)
 * )
 */
class MaintenanceLog extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'asset_id',
        'schedule_id',
        'type',
        'title',
        'description',
        'performed_by',
        'performed_at',
        'duration',
        'duration_unit',
        'cost',
        'parts_used',
        'status',
        'notes',
        'metadata'
    ];

    protected $casts = [
        'performed_at' => 'datetime',
        'cost' => 'float',
        'parts_used' => 'array',
        'metadata' => 'array'
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(MaintenanceSchedule::class, 'schedule_id');
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(MaintenanceLogAttachment::class);
    }
} 