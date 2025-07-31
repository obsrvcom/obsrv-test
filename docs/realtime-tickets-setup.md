# Real-time Ticket Support Setup

This guide covers setting up and testing the real-time functionality for support tickets using Laravel Reverb.

## Prerequisites

- Laravel Reverb is already configured in the project
- Pusher JS and Laravel Echo are already installed

## Environment Configuration

Add these environment variables to your `.env` file:

```env
# Broadcasting
BROADCAST_CONNECTION=reverb

# Reverb Configuration  
REVERB_APP_ID=your-app-id
REVERB_APP_KEY=your-app-key
REVERB_APP_SECRET=your-app-secret
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http

# For Vite (frontend)
VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

## Starting the Services

1. **Start Laravel Reverb server:**
   ```bash
   php artisan reverb:start
   ```

2. **Start your development server:**
   ```bash
   php artisan serve
   ```

3. **Build frontend assets:**
   ```bash
   npm run dev
   ```

## Testing Real-time Functionality

### Test Scenario 1: Customer sends message → Company sees it immediately

1. Open Site view in one browser tab: `/app/site/1/tickets/[ticket-id]`
2. Open Company view in another tab: `/app/company/1/tickets/[ticket-id]`
3. Send a message from the Site view
4. Verify the message appears immediately in the Company view

### Test Scenario 2: Company responds → Customer sees it immediately

1. Keep both tabs open from Test 1
2. Send a response from the Company view
3. Verify the response appears immediately in the Site view

### Test Scenario 3: Status changes are reflected in real-time

1. Keep both tabs open
2. Change the ticket status from the Company view
3. Verify the status updates immediately in the Site view

### Test Scenario 4: Assignment changes are reflected

1. Keep both tabs open
2. Assign the ticket to a team/user from the Company view
3. Verify the assignment info updates in the Site view

### Test Scenario 5: Ticket list updates

1. Open Site tickets list: `/app/site/1/tickets`
2. Open Company tickets list: `/app/company/1/tickets`
3. Create a new ticket from Site view
4. Verify it appears in Company tickets list immediately
5. Update a ticket (status, assignment) from Company view
6. Verify changes reflect in Site tickets list immediately

## How It Works

### Events Broadcast

- **TicketUpdated**: Fires whenever any change happens to a ticket (new message, status change, assignment change)

### Channels Used

- `ticket.{ticketId}` - Updates for specific ticket
- `site.{siteId}.tickets` - All ticket updates for a site
- `company.{companyId}.tickets` - All ticket updates for a company

### Livewire Native Broadcasting

Each Livewire component uses the `protected $listeners` array to listen for Echo events:

```php
protected $listeners = [
    'echo-private:ticket.{ticket.id},.ticket.updated' => 'refreshTicket',
    'echo-private:site.{site.id}.tickets,.ticket.updated' => 'handleSiteTicketUpdate',
];
```

This approach:
- Uses Livewire's built-in Laravel Echo integration
- No manual JavaScript required
- Automatic component refresh when events are received
- Clean, declarative event handling

## Troubleshooting

### No real-time updates

For detailed debugging steps, see `docs/debugging-realtime-tickets.md`

Quick checklist:
1. Check that Reverb server is running: `php artisan reverb:start`
2. Verify `.env` variables are set correctly
3. Check browser console for WebSocket connection errors
4. Ensure user has proper channel authorization (check `routes/channels.php`)
5. Use the "Test Broadcast" buttons to manually trigger events

### Connection issues

1. Make sure `REVERB_HOST` and `REVERB_PORT` match your setup
2. For production, update `REVERB_SCHEME` to `https`
3. Check firewall settings for WebSocket port access

### Authorization failures

- Verify user has access to the site/company for the channels they're trying to access
- Check `routes/channels.php` authorization logic

## Production Considerations

1. Use HTTPS (`REVERB_SCHEME=https`)
2. Configure proper Redis scaling if needed
3. Set up SSL certificates for WebSocket connections
4. Consider using a reverse proxy (nginx) for WebSocket connections 
