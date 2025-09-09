<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Agent extends Model
{
    protected $fillable = [
        'device_id',
        'thing_name',
        'name',
        'type',
        'site_id',
        'status',
        'firmware_version',
        'ip_address',
        'config',
        'knx_monitors',
        'last_heartbeat_at',
        'paired_at',
        'provisioned_at',
    ];

    protected $casts = [
        'config' => 'array',
        'knx_monitors' => 'array',
        'last_heartbeat_at' => 'datetime',
        'paired_at' => 'datetime',
        'provisioned_at' => 'datetime',
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function heartbeats(): HasMany
    {
        return $this->hasMany(AgentHeartbeat::class);
    }

    public function telegrams(): HasMany
    {
        return $this->hasMany(Telegram::class, 'device_id', 'device_id');
    }

    public function latestHeartbeat()
    {
        return $this->hasOne(AgentHeartbeat::class)->latestOfMany();
    }

    public function isOnline(): bool
    {
        if (!$this->last_heartbeat_at) {
            return false;
        }

        // Consider agent offline if no heartbeat in last 5 minutes
        return $this->last_heartbeat_at->isAfter(now()->subMinutes(5));
    }

    public function updateStatus(): void
    {
        $status = $this->isOnline() ? 'online' : 'offline';
        
        if ($this->status !== $status) {
            $this->update(['status' => $status]);
        }
    }
}
