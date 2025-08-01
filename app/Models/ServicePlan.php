<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ServicePlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_plan_group_id',
        'name',
        'description',
        'is_active',
        'is_featured',
        'sort_order',
        'color',
        'base_price_monthly',
        'base_price_quarterly',
        'base_price_annually',
        'minimum_contract_months',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'sort_order' => 'integer',
        'base_price_monthly' => 'decimal:2',
        'base_price_quarterly' => 'decimal:2',
        'base_price_annually' => 'decimal:2',
        'minimum_contract_months' => 'integer',
    ];

    /**
     * Get the service plan group that owns this plan.
     */
    public function servicePlanGroup(): BelongsTo
    {
        return $this->belongsTo(ServicePlanGroup::class);
    }

    /**
     * Get the company through the service plan group.
     */
    public function company(): BelongsTo
    {
        return $this->servicePlanGroup->company();
    }

    /**
     * Get the feature values for this plan.
     */
    public function featureValues(): HasMany
    {
        return $this->hasMany(ServicePlanFeatureValue::class);
    }

    /**
     * Get the features for this plan through feature values.
     */
    public function features(): BelongsToMany
    {
        return $this->belongsToMany(ServicePlanFeature::class, 'service_plan_feature_values')
                    ->withPivot(['value', 'is_included', 'display_value'])
                    ->withTimestamps();
    }

    /**
     * Get a specific feature value for this plan.
     */
    public function getFeatureValue(ServicePlanFeature $feature)
    {
        return $this->featureValues()
                    ->where('service_plan_feature_id', $feature->id)
                    ->first();
    }

    /**
     * Set a feature value for this plan.
     */
    public function setFeatureValue(ServicePlanFeature $feature, $value, $isIncluded = false, $displayValue = null)
    {
        return $this->featureValues()->updateOrCreate(
            ['service_plan_feature_id' => $feature->id],
            [
                'value' => is_array($value) ? json_encode($value) : $value,
                'is_included' => $isIncluded,
                'display_value' => $displayValue ?? $value,
            ]
        );
    }

    /**
     * Scope query to only include active plans.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope query to only include featured plans.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope query to order by sort order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    /**
     * Get formatted price for a given billing period.
     */
    public function getFormattedPrice(string $period = 'monthly'): string
    {
        $price = match ($period) {
            'quarterly' => $this->base_price_quarterly,
            'annually' => $this->base_price_annually,
            default => $this->base_price_monthly,
        };

        return $price ? '£' . number_format($price, 2) : '-';
    }
}
