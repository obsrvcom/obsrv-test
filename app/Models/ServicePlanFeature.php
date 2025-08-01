<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ServicePlanFeature extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_plan_feature_category_id',
        'name',
        'description',
        'data_type',
        'options',
        'is_active',
        'affects_sla',
        'sort_order',
        'unit',
    ];

    protected $casts = [
        'options' => 'array',
        'is_active' => 'boolean',
        'affects_sla' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get the feature category that owns this feature.
     */
    public function featureCategory(): BelongsTo
    {
        return $this->belongsTo(ServicePlanFeatureCategory::class, 'service_plan_feature_category_id');
    }

    /**
     * Get the company through the feature category.
     */
    public function company(): BelongsTo
    {
        return $this->featureCategory->company();
    }

    /**
     * Get the feature values for this feature.
     */
    public function featureValues(): HasMany
    {
        return $this->hasMany(ServicePlanFeatureValue::class);
    }

    /**
     * Get the service plans that have this feature.
     */
    public function servicePlans(): BelongsToMany
    {
        return $this->belongsToMany(ServicePlan::class, 'service_plan_feature_values')
                    ->withPivot(['value', 'is_included', 'display_value'])
                    ->withTimestamps();
    }

    /**
     * Scope query to only include active features.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope query to only include SLA-affecting features.
     */
    public function scopeAffectsSla($query)
    {
        return $query->where('affects_sla', true);
    }

    /**
     * Scope query to order by sort order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    /**
     * Get the formatted display value for a given raw value.
     */
    public function getFormattedValue($value, $isIncluded = false): string
    {
        if ($this->data_type === 'boolean') {
            return $isIncluded ? '✓' : '-';
        }

        if ($this->data_type === 'currency') {
            return $value ? '£' . number_format((float) $value, 2) : '-';
        }

        if ($this->data_type === 'select' && $this->options) {
            $optionKey = $value;
            return $this->options[$optionKey] ?? $value ?? '-';
        }

        $formatted = $value ?? '-';

        if ($this->unit && $value) {
            $formatted .= ' ' . $this->unit;
        }

        return $formatted;
    }
}
