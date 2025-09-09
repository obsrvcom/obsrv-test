<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgentHeartbeat extends Model
{
    protected $fillable = [
        'agent_id',
        'status',
        'metrics',
        'knx_status',
        'uptime',
        'ip_address',
    ];

    protected $casts = [
        'metrics' => 'array',
        'knx_status' => 'array',
    ];

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }
}
