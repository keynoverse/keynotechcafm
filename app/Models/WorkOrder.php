<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'number',
        'title',
        'description',
        'priority',
        'status',
        'requested_by',
        'assigned_to',
        'asset_id',
        'space_id',
        'due_date',
        'started_at',
        'completed_at',
        'metadata'
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'metadata' => 'json'
    ];

    // Relationships
    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function space()
    {
        return $this->belongsTo(Space::class);
    }

    public function comments()
    {
        return $this->hasMany(WorkOrderComment::class);
    }

    public function attachments()
    {
        return $this->hasMany(WorkOrderAttachment::class);
    }
} 