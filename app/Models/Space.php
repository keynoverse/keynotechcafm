<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Space extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'floor_id',
        'name',
        'code',
        'type',
        'area',
        'capacity',
        'status',
        'metadata'
    ];

    protected $casts = [
        'area' => 'decimal:2',
        'capacity' => 'integer',
        'metadata' => 'json'
    ];

    // Relationships
    public function floor()
    {
        return $this->belongsTo(Floor::class);
    }

    public function building()
    {
        return $this->hasOneThrough(Building::class, Floor::class, 'id', 'id', 'floor_id', 'building_id');
    }

    public function assets()
    {
        return $this->hasMany(Asset::class);
    }

    public function workOrders()
    {
        return $this->hasMany(WorkOrder::class);
    }
} 