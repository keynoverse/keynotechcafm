<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id',
        'type',
        'frequency',
        'schedule_config',
        'last_maintenance',
        'next_maintenance'
    ];

    protected $casts = [
        'schedule_config' => 'json',
        'last_maintenance' => 'date',
        'next_maintenance' => 'date'
    ];

    // Relationships
    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function maintenanceLogs()
    {
        return $this->hasMany(MaintenanceLog::class, 'schedule_id');
    }
} 