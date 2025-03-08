<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Kalnoy\Nestedset\NodeTrait;

class AssetCategory extends Model
{
    use HasFactory, NodeTrait;

    protected $fillable = [
        'name',
        'code',
        'description',
        'parent_id'
    ];

    // Relationships
    public function assets()
    {
        return $this->hasMany(Asset::class, 'category_id');
    }

    // Helper method to get all assets in this category and its descendants
    public function allAssets()
    {
        $descendantIds = $this->descendants()->pluck('id');
        $ids = collect([$this->id])->concat($descendantIds);
        
        return Asset::whereIn('category_id', $ids);
    }
} 