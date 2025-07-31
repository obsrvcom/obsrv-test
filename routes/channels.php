<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\User;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Ticket-specific channels
Broadcast::channel('ticket.{ticketId}', function (User $user, $ticketId) {
    $ticket = \App\Models\Ticket::find($ticketId);
    if (!$ticket) {
        return false;
    }

    // Check site access
    if ($user->sites()->where('sites.id', $ticket->site_id)->exists()) {
        return true;
    }

    // Check company access
    if ($user->companies()->where('companies.id', $ticket->site->company_id)->exists()) {
        return true;
    }

    return false;
});
