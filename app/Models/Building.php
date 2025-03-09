<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Building extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'address',
        'status',
        'year_built',
        'total_area',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array',
        'year_built' => 'integer',
        'total_area' => 'decimal:2'
    ];

    // Relationships
    public function floors()
    {
        return $this->hasMany(Floor::class);
    }

    public function spaces()
    {
        return $this->hasManyThrough(Space::class, Floor::class);
    }

    public function assets()
    {
        return $this->morphMany(Asset::class, 'assetable');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInMaintenance($query)
    {
        return $query->where('status', 'maintenance');
    }

    // Accessors & Mutators
    protected function status(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => ucfirst($value),
            set: fn (string $value) => strtolower($value),
        );
    }
} 