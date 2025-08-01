<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketMessageRead extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_message_id',
        'user_id',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function ticketMessage(): BelongsTo
    {
        return $this->belongsTo(TicketMessage::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Create a read record for a user and message
     */
    public static function markAsRead(int $ticketMessageId, int $userId): self
    {
        return static::firstOrCreate(
            [
                'ticket_message_id' => $ticketMessageId,
                'user_id' => $userId,
            ],
            [
                'read_at' => now(),
            ]
        );
    }

    /**
     * Get all users who have read a specific message
     */
    public static function getUsersWhoRead(int $ticketMessageId)
    {
        return static::with('user')
            ->where('ticket_message_id', $ticketMessageId)
            ->get()
            ->pluck('user');
    }
}
