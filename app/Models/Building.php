<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Building extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'status',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'json'
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
} 