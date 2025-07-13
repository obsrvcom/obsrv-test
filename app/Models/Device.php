<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    protected $fillable = [
        'name',
        'type',
        'user_id',
        'revoked',
        'last_seen',
        'session_id',
        'user_agent',
        'ip_address',
        'fingerprint',
    ];

    protected $casts = [
        'last_seen' => 'datetime',
        'revoked' => 'boolean',
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
