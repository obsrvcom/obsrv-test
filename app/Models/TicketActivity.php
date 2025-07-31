<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'user_id',
        'activity_type',
        'description',
        'old_value',
        'new_value',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeRecent($query, int $limit = 50)
    {
        return $query->latest()->limit($limit);
    }

    public function scopeByType($query, string $activityType)
    {
        return $query->where('activity_type', $activityType);
    }
}
