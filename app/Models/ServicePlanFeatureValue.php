<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServicePlanFeatureValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_plan_id',
        'service_plan_feature_id',
        'value',
        'is_included',
        'display_value',
    ];

    protected $casts = [
        'is_included' => 'boolean',
    ];

    /**
     * Get the service plan that owns this feature value.
     */
    public function servicePlan(): BelongsTo
    {
        return $this->belongsTo(ServicePlan::class);
    }

    /**
     * Get the feature that this value belongs to.
     */
    public function feature(): BelongsTo
    {
        return $this->belongsTo(ServicePlanFeature::class, 'service_plan_feature_id');
    }

    /**
     * Get the parsed value (decode JSON if needed).
     */
    public function getParsedValueAttribute()
    {
        $value = $this->value;

        if (is_string($value) && $this->isJson($value)) {
            return json_decode($value, true);
        }

        return $value;
    }

    /**
     * Get the formatted display value.
     */
    public function getFormattedValueAttribute(): string
    {
        if ($this->display_value) {
            return $this->display_value;
        }

        return $this->feature->getFormattedValue($this->value, $this->is_included);
    }

    /**
     * Check if a string is valid JSON.
     */
    private function isJson($string): bool
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
}
