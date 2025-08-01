<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'user_id',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Subscribe a user to a ticket
     */
    public static function subscribe(int $ticketId, int $userId): self
    {
        return static::firstOrCreate([
            'ticket_id' => $ticketId,
            'user_id' => $userId,
        ]);
    }

    /**
     * Unsubscribe a user from a ticket
     */
    public static function unsubscribe(int $ticketId, int $userId): bool
    {
        return static::where('ticket_id', $ticketId)
            ->where('user_id', $userId)
            ->delete() > 0;
    }

    /**
     * Check if a user is subscribed to a ticket
     */
    public static function isSubscribed(int $ticketId, int $userId): bool
    {
        return static::where('ticket_id', $ticketId)
            ->where('user_id', $userId)
            ->exists();
    }
}
