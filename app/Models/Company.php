<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'avatar',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class)->withPivot('role', 'job_title')->withTimestamps();
    }

    public function sites()
    {
        return $this->hasMany(Site::class);
    }

    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }

    public function siteGroups()
    {
        return $this->hasMany(SiteGroup::class);
    }

    public function contactGroups()
    {
        return $this->hasMany(ContactGroup::class);
    }

    public function teams()
    {
        return $this->hasMany(Team::class);
    }

    public function tickets()
    {
        return $this->hasManyThrough(Ticket::class, Site::class);
    }

    public function servicePlanGroups()
    {
        return $this->hasMany(ServicePlanGroup::class);
    }

    public function servicePlanFeatureCategories()
    {
        return $this->hasMany(ServicePlanFeatureCategory::class);
    }

    // New service plans structure relationships
    public function servicePlansNew()
    {
        return $this->hasMany(ServicePlanNew::class);
    }

    public function servicePlanFeatureGroupsNew()
    {
        return $this->hasMany(ServicePlanFeatureGroupNew::class);
    }

    public function planCategories()
    {
        return $this->hasMany(PlanCategory::class);
    }

    public function activePlanCategories()
    {
        return $this->planCategories()->where('is_active', true);
    }

    public function hasUser(User $user)
    {
        return $this->users()->where('user_id', $user->id)->exists();
    }

    public function getUserRole(User $user)
    {
        $pivot = $this->users()->where('user_id', $user->id)->first()?->pivot;
        return $pivot ? $pivot->role : null;
    }

    public function isUserAdmin(User $user)
    {
        $role = $this->getUserRole($user);
        return in_array($role, ['admin', 'owner']);
    }

    public function getAvatarUrl()
    {
        if ($this->avatar) {
            return asset('storage/' . $this->avatar);
        }
        return null;
    }

    public function getAvatarOrInitials()
    {
        if ($this->avatar) {
            return ['type' => 'image', 'src' => $this->getAvatarUrl()];
        }
        return ['type' => 'initials', 'initials' => substr($this->name, 0, 2)];
    }
}
