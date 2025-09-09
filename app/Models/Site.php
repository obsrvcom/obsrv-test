<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Site extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'company_id',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function siteChats()
    {
        return $this->hasMany(SiteChat::class)->orderBy('created_at', 'desc');
    }

    public function chatSessions()
    {
        return $this->hasMany(ChatSession::class);
    }

    public function siteGroups()
    {
        return $this->belongsToMany(SiteGroup::class);
    }

    public function getActiveChatSession()
    {
        return $this->chatSessions()
            ->whereIn('status', ['open', 'awaiting_customer', 'on_hold'])
            ->latest()
            ->first();
    }

    // Backward compatibility method
    public function getOpenChatSession()
    {
        return $this->getActiveChatSession();
    }

    public function hasOpenChatSession()
    {
        return $this->chatSessions()->where('status', 'open')->exists();
    }

    public function agents()
    {
        return $this->hasMany(Agent::class);
    }
}
