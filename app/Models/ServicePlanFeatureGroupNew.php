<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServicePlanFeatureGroupNew extends Model
{
    use HasFactory;

    protected $table = 'service_plan_feature_groups_new';

    protected $fillable = [
        'company_id',
        'category_id',
        'name',
        'description',
        'is_active',
        'sort_order',
        'color',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(PlanCategory::class);
    }

    public function features(): HasMany
    {
        return $this->hasMany(ServicePlanFeatureNew::class, 'feature_group_id')->orderBy('sort_order');
    }

    public function activeFeatures(): HasMany
    {
        return $this->features()->where('is_active', true);
    }

    public function levels(): BelongsToMany
    {
        return $this->belongsToMany(
            ServicePlanLevel::class,
            'service_plan_level_feature_groups',
            'feature_group_id',
            'service_plan_level_id'
        )->withPivot(['is_included', 'sort_order'])->withTimestamps();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    // Helper methods
    public function isUsedInLevels(): bool
    {
        return $this->levels()->exists();
    }

    public function getUsageCount(): int
    {
        return $this->levels()->count();
    }
}
