<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'color',
        'company_id',
        'allow_direct_tickets',
    ];

    protected $casts = [
        'allow_direct_tickets' => 'boolean',
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
        return $this->hasMany(Ticket::class, 'assigned_team_id');
    }

    public function hasUser(User $user)
    {
        return $this->users()->where('user_id', $user->id)->exists();
    }

    public function scopeAllowingDirectTickets($query)
    {
        return $query->where('allow_direct_tickets', true);
    }
}
