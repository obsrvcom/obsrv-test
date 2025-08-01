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

    // Check if user has site access
    $hasSiteAccess = $user->sites()->where('sites.id', $ticket->site_id)->exists();
    // Check if user has company access
    $hasCompanyAccess = $user->companies()->where('companies.id', $ticket->site->company_id)->exists();

    if (!$hasSiteAccess && !$hasCompanyAccess) {
        return false;
    }

        // Determine user type based on access context from request
    $request = request();
    $routeName = $request->route() ? $request->route()->getName() : '';

    // Site routes start with 'site.', company routes don't have this prefix
    $isSiteContext = str_starts_with($routeName, 'site.');

    // If accessing via site route, treat as customer
    // If accessing via company route (no 'site.' prefix), treat as company user (if they have company access)
    $isCompanyUser = !$isSiteContext && $hasCompanyAccess;

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
});
