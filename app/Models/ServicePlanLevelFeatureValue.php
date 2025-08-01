<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServicePlanLevelFeatureValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_plan_level_id',
        'feature_id',
        'value',
        'is_included',
        'display_value',
    ];

    protected $casts = [
        'is_included' => 'boolean',
    ];

    // Relationships
    public function level(): BelongsTo
    {
        return $this->belongsTo(ServicePlanLevel::class, 'service_plan_level_id');
    }

    public function feature(): BelongsTo
    {
        return $this->belongsTo(ServicePlanFeatureNew::class, 'feature_id');
    }

    // Helper methods
    public function getParsedValueAttribute()
    {
        if (empty($this->value)) {
            return null;
        }

        // Try to decode JSON, return original value if not JSON
        $decoded = json_decode($this->value, true);
        return json_last_error() === JSON_ERROR_NONE ? $decoded : $this->value;
    }

    public function getFormattedValueAttribute(): string
    {
        if ($this->display_value) {
            return $this->display_value;
        }

        return $this->feature->getFormattedValue($this->parsed_value, $this->is_included);
    }

    private function isJson($string): bool
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }

    public function setValueForDataType($value, $dataType = null)
    {
        $dataType = $dataType ?? $this->feature->data_type;

        switch ($dataType) {
            case 'boolean':
                $this->is_included = (bool) $value;
                $this->value = null;
                break;
            case 'select':
            case 'text':
                $this->value = (string) $value;
                break;
            case 'number':
            case 'currency':
                $this->value = is_numeric($value) ? $value : null;
                break;
            case 'time':
                $this->value = (string) $value;
                break;
            default:
                $this->value = $value;
        }
    }
}
