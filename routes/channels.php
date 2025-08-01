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

// Ticket presence channels - track who's viewing a ticket
Broadcast::channel('ticket-presence.{ticketId}', function (User $user, $ticketId) {
    $ticket = \App\Models\Ticket::find($ticketId);
    if (!$ticket) {
        return false;
    }

    // Check access (same logic as above)
    $hasAccess = $user->sites()->where('sites.id', $ticket->site_id)->exists() ||
                 $user->companies()->where('companies.id', $ticket->site->company_id)->exists();

    if ($hasAccess) {
        $isCompanyUser = $user->companies()->where('companies.id', $ticket->site->company_id)->exists();

        // Return user data for presence
        $userData = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'initials' => $user->initials(),
            'type' => $isCompanyUser ? 'company' : 'customer'
        ];

        // Add job title for company users
        if ($isCompanyUser) {
            $companyUser = $user->companies()->where('companies.id', $ticket->site->company_id)->first();
            $userData['job_title'] = $companyUser->pivot->job_title ?? null;
        }

        return $userData;
    }

    return false;
});
