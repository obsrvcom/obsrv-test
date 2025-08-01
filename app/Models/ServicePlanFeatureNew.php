<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServicePlanFeatureNew extends Model
{
    use HasFactory;

    protected $table = 'service_plan_features_new';

    protected $fillable = [
        'feature_group_id',
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

    // Relationships
    public function featureGroup(): BelongsTo
    {
        return $this->belongsTo(ServicePlanFeatureGroupNew::class, 'feature_group_id');
    }

    public function company(): BelongsTo
    {
        return $this->featureGroup->company();
    }

    public function featureValues(): HasMany
    {
        return $this->hasMany(ServicePlanLevelFeatureValue::class, 'feature_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAffectsSla($query)
    {
        return $query->where('affects_sla', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    // Helper methods
    public function getFormattedValue($value, $isIncluded = false): string
    {
        if ($this->data_type === 'boolean') {
            return $isIncluded ? '✓' : '✗';
        }

        if (empty($value)) {
            return $isIncluded ? '✓' : '✗';
        }

        $formattedValue = match($this->data_type) {
            'currency' => '£' . number_format((float) $value, 2),
            'number' => number_format((float) $value),
            'time' => $value . ($this->unit ? ' ' . $this->unit : ''),
            default => (string) $value,
        };

        return $this->unit && $this->data_type !== 'currency' && $this->data_type !== 'time'
            ? $formattedValue . ' ' . $this->unit
            : $formattedValue;
    }

    public function isBoolean(): bool
    {
        return $this->data_type === 'boolean';
    }

    public function isText(): bool
    {
        return $this->data_type === 'text';
    }

    public function isNumber(): bool
    {
        return $this->data_type === 'number';
    }

    public function isCurrency(): bool
    {
        return $this->data_type === 'currency';
    }

    public function isTime(): bool
    {
        return $this->data_type === 'time';
    }

    public function isSelect(): bool
    {
        return $this->data_type === 'select';
    }

    public function getSelectOptions(): array
    {
        return $this->isSelect() ? ($this->options ?? []) : [];
    }
}
