<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServicePlanRevision extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_plan_id',
        'name',
        'description',
        'status',
        'is_current',
        'version_number',
        'metadata',
        'published_at',
    ];

    protected $casts = [
        'is_current' => 'boolean',
        'version_number' => 'integer',
        'metadata' => 'array',
        'published_at' => 'datetime',
    ];

    // Relationships
    public function servicePlan(): BelongsTo
    {
        return $this->belongsTo(ServicePlanNew::class, 'service_plan_id');
    }

    public function levels(): HasMany
    {
        return $this->hasMany(ServicePlanLevel::class)->orderBy('sort_order');
    }

    public function activeLevels(): HasMany
    {
        return $this->levels()->where('is_active', true);
    }

    // Scopes
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeArchived($query)
    {
        return $query->where('status', 'archived');
    }

    public function scopeCurrent($query)
    {
        return $query->where('is_current', true);
    }

    // Helper methods
    public function publish()
    {
        // Mark other revisions as not current
        $this->servicePlan->revisions()->update(['is_current' => false]);

        // Update this revision
        $this->update([
            'status' => 'published',
            'is_current' => true,
            'published_at' => now(),
        ]);
    }

    public function archive()
    {
        $this->update(['status' => 'archived', 'is_current' => false]);
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function isArchived(): bool
    {
        return $this->status === 'archived';
    }
}
