<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServicePlanLevel extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_plan_revision_id',
        'name',
        'description',
        'is_active',
        'is_featured',
        'sort_order',
        'color',
        'monthly_price',
        'quarterly_price',
        'annual_price',
        'minimum_contract_months',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'sort_order' => 'integer',
        'monthly_price' => 'decimal:2',
        'quarterly_price' => 'decimal:2',
        'annual_price' => 'decimal:2',
        'minimum_contract_months' => 'integer',
    ];

    // Relationships
    public function revision(): BelongsTo
    {
        return $this->belongsTo(ServicePlanRevision::class, 'service_plan_revision_id');
    }

    public function featureGroups(): BelongsToMany
    {
        return $this->belongsToMany(
            ServicePlanFeatureGroupNew::class,
            'service_plan_level_feature_groups',
            'service_plan_level_id',
            'feature_group_id'
        )->withPivot(['is_included', 'sort_order'])->withTimestamps();
    }

    public function includedFeatureGroups(): BelongsToMany
    {
        return $this->featureGroups()->wherePivot('is_included', true);
    }

    public function featureValues(): HasMany
    {
        return $this->hasMany(ServicePlanLevelFeatureValue::class, 'service_plan_level_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    // Helper methods
    public function getFormattedPrice(string $period = 'monthly'): string
    {
        $price = match($period) {
            'quarterly' => $this->quarterly_price,
            'annual' => $this->annual_price,
            default => $this->monthly_price,
        };

        return $price ? '£' . number_format($price, 2) : 'N/A';
    }

    public function hasFeatureGroup(ServicePlanFeatureGroupNew $featureGroup): bool
    {
        return $this->featureGroups()->where('feature_group_id', $featureGroup->id)->exists();
    }

    public function attachFeatureGroup(ServicePlanFeatureGroupNew $featureGroup, bool $isIncluded = true, int $sortOrder = 0)
    {
        $this->featureGroups()->attach($featureGroup->id, [
            'is_included' => $isIncluded,
            'sort_order' => $sortOrder,
        ]);
    }

    public function detachFeatureGroup(ServicePlanFeatureGroupNew $featureGroup)
    {
        $this->featureGroups()->detach($featureGroup->id);
    }
}
