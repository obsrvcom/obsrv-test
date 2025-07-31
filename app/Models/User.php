<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    /**
     * Get the devices for the user.
     */
    public function devices()
    {
        return $this->hasMany(Device::class);
    }

    /**
     * Get the companies for the user.
     */
    public function companies()
    {
        return $this->belongsToMany(Company::class)->withPivot('role', 'job_title')->withTimestamps();
    }

        /**
     * Get the current company for the user (from session).
     */
    public function currentCompany()
    {
        $companyId = session('current_company_id');
        if (!$companyId) {
            return null;
        }

        return $this->companies()->where('company_id', $companyId)->first();
    }

    /**
     * Get the current company from request (for subdomain-based access).
     */
    public function currentCompanyFromRequest()
    {
        $request = request();
        $company = $request->attributes->get('current_company');

        if ($company && $this->companies()->where('company_id', $company->id)->exists()) {
            return $company;
        }

        return null;
    }

    public function sites()
    {
        return $this->belongsToMany(Site::class)->withTimestamps();
    }

    public function teams()
    {
        return $this->belongsToMany(Team::class)->withTimestamps();
    }

    public function siteChats()
    {
        return $this->hasMany(\App\Models\SiteChat::class);
    }

    public function canAccessSite(Site $site)
    {
        // Direct site user
        if ($this->sites()->where('site_id', $site->id)->exists()) {
            return true;
        }
        // Company member
        if ($site->company && $site->company->hasUser($this)) {
            return true;
        }
        return false;
    }

    public function accessibleSites()
    {
        // Get all sites the user can access: direct or via company
        $siteIds = $this->sites()->pluck('sites.id')->toArray();
        $companyIds = $this->companies()->pluck('companies.id')->toArray();
        $companySites = \App\Models\Site::whereIn('company_id', $companyIds)->pluck('id')->toArray();
        $allSiteIds = array_unique(array_merge($siteIds, $companySites));
        return \App\Models\Site::whereIn('id', $allSiteIds)->get();
    }
}
