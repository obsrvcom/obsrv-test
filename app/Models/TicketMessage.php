<?php

namespace App\Models;

use App\Events\TicketUpdated;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'user_id',
        'message_type',
        'content',
        'attachments',
    ];

    protected $casts = [
        'attachments' => 'array',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::created(function ($message) {
            $ticket = $message->ticket;

            // Update last message timestamps
            if ($message->message_type === 'customer') {
                $ticket->update(['last_customer_message_at' => $message->created_at]);
            } elseif ($message->message_type === 'company') {
                $ticket->update(['last_company_message_at' => $message->created_at]);
            }

            // Auto-update status if customer responds
            if ($message->message_type === 'customer' && $ticket->status === 'awaiting_customer') {
                $ticket->updateStatus('open', $message->user_id);
            }

            // Broadcast the ticket update event
            \Log::info('Broadcasting ticket update', ['ticket_id' => $message->ticket->id]);
            broadcast(new TicketUpdated($message->ticket));
        });
    }

    public function isCustomerMessage(): bool
    {
        return $this->message_type === 'customer';
    }

    public function isCompanyMessage(): bool
    {
        return $this->message_type === 'company';
    }

    public function isInternalMessage(): bool
    {
        return $this->message_type === 'internal';
    }
}
