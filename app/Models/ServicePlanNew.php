<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServicePlanNew extends Model
{
    use HasFactory;

    protected $table = 'service_plans_new';

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

    public function revisions(): HasMany
    {
        return $this->hasMany(ServicePlanRevision::class, 'service_plan_id')->orderBy('version_number', 'desc');
    }

    public function currentRevision(): HasMany
    {
        return $this->revisions()->where('is_current', true);
    }

    public function publishedRevisions(): HasMany
    {
        return $this->revisions()->where('status', 'published');
    }

    public function draftRevisions(): HasMany
    {
        return $this->revisions()->where('status', 'draft');
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
    public function getCurrentRevision()
    {
        return $this->revisions()->where('is_current', true)->first();
    }

    public function getLatestRevision()
    {
        return $this->revisions()->first(); // Already ordered by version_number desc
    }
}
