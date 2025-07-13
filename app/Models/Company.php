<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'subdomain',
        'description',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class)->withPivot('role')->withTimestamps();
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
        return in_array($this->getUserRole($user), ['admin', 'owner']);
    }

    public function isUserOwner(User $user)
    {
        return $this->getUserRole($user) === 'owner';
    }
}
