<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Auth\Authenticatable;

/**
 * @property string $uuid
 */
class Device extends Model implements AuthenticatableContract
{
    use HasFactory, HasApiTokens, Authenticatable;

    protected $fillable = [
        'name',
        'uuid',
        'type',
        'user_id',
        'revoked',
        'last_seen',
        'session_id',
        'user_agent',
        'ip_address',
        'fingerprint',
        'notifications_enabled',
    ];

    protected $casts = [
        'last_seen' => 'datetime',
        'revoked' => 'boolean',
        'uuid' => 'string',
        'notifications_enabled' => 'boolean',
    ];

    public function scopeBySession($query, $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }

    public function isWebBrowser()
    {
        return !empty($this->session_id);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
