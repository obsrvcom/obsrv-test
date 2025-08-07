<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class PlanCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'name',
        'description',
        'slug',
        'is_active',
        'sort_order',
        'color',
        'icon',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    // Automatically generate slug when creating
    protected static function booted()
    {
        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });

        static::updating(function ($category) {
            if ($category->isDirty('name') && empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function plans(): HasMany
    {
        return $this->hasMany(ServicePlanNew::class, 'category_id');
    }

    public function activePlans(): HasMany
    {
        return $this->plans()->where('is_active', true);
    }

    public function featureGroups(): HasMany
    {
        return $this->hasMany(ServicePlanFeatureGroupNew::class, 'category_id');
    }

    public function activeFeatureGroups(): HasMany
    {
        return $this->featureGroups()->where('is_active', true);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    // Helper methods
    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function getTotalPlansCount(): int
    {
        return $this->plans()->count();
    }

    public function getActivePlansCount(): int
    {
        return $this->activePlans()->count();
    }

    public function getTotalFeaturesCount(): int
    {
        return $this->featureGroups()
            ->withCount(['features' => function($query) {
                $query->where('is_active', true);
            }])
            ->get()
            ->sum('features_count');
    }
}