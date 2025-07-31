<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketDraft extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'user_id',
        'draft_type',
        'content',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForTicket($query, int $ticketId)
    {
        return $query->where('ticket_id', $ticketId);
    }

    public function scopeCustomerDrafts($query)
    {
        return $query->where('draft_type', 'customer');
    }

    public function scopeRecentFirst($query)
    {
        return $query->latest('updated_at');
    }
}
