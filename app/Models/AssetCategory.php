<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kalnoy\Nestedset\NodeTrait;

/**
 * @OA\Schema(
 *     schema="AssetCategory",
 *     required={"id", "name", "status"},
 *     @OA\Property(property="id", type="integer", format="int64", example=1),
 *     @OA\Property(property="name", type="string", example="Electronics"),
 *     @OA\Property(property="description", type="string", nullable=true, example="Electronic equipment and devices"),
 *     @OA\Property(property="parent_id", type="integer", format="int64", nullable=true, example=null),
 *     @OA\Property(property="status", type="string", enum={"active", "inactive"}, example="active"),
 *     @OA\Property(property="metadata", type="object", nullable=true),
 *     @OA\Property(property="_lft", type="integer", example=1),
 *     @OA\Property(property="_rgt", type="integer", example=2),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true)
 * )
 */
class AssetCategory extends Model
{
    use HasFactory, SoftDeletes, NodeTrait;

    protected $fillable = [
        'name',
        'description',
        'parent_id',
        'status',
        'metadata',
        '_lft',
        '_rgt'
    ];

    protected $casts = [
        'metadata' => 'array'
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(AssetCategory::class, 'parent_id');
    }

    public function assets(): HasMany
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