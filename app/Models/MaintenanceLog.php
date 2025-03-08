<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id',
        'schedule_id',
        'performed_by',
        'type',
        'description',
        'status',
        'cost',
        'parts_used'
    ];

    protected $casts = [
        'cost' => 'decimal:2',
        'parts_used' => 'json'
    ];

    // Relationships
    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function schedule()
    {
        return $this->belongsTo(MaintenanceSchedule::class, 'schedule_id');
    }

    public function performer()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
} 