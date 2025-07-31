# Debugging Real-time Tickets WebSocket Issues

This guide helps you debug WebSocket subscription problems with the real-time ticket system.

## Step 1: Verify Laravel Reverb is Running

First, make sure Reverb is running:

```bash
php artisan reverb:start
```

You should see output like:
```
Starting Reverb server...  
Reverb server started on 127.0.0.1:8080
```

## Step 2: Check Environment Variables

Verify these environment variables are set in your `.env`:

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

## Step 3: Check Browser Console

1. Open browser developer tools (F12)
2. Go to Console tab
3. Load a ticket view page
4. Look for these messages:

✅ **Success indicators:**
```
Echo is available
Attempting to subscribe to channels:
- ticket.123
- site.1.tickets
```

❌ **Error indicators:**
```
Echo is not available - WebSocket connection failed
WebSocket connection failed
```

## Step 4: Check WebSocket Connection Status

When you load a ticket page, you should see a small green notification saying "WebSocket Connected" in the bottom right corner for 3 seconds.

If you see a red "WebSocket Not Connected" notification, the issue is with the basic WebSocket setup.

## Step 5: Test Manual Broadcasting

I've added "Test Broadcast" buttons to both site and company ticket views. Use these to manually trigger events:

1. Open site ticket view in one tab
2. Open company ticket view in another tab  
3. Click "Test Broadcast" in either view
4. Check if the other view refreshes
5. Check Laravel logs for broadcast messages

## Step 6: Check Channel Authorization

The most common issue is channel authorization. Users need proper access to subscribe to channels.

### Debug User Access

Add this temporary method to your User model to debug access:

```php
public function debugChannelAccess($ticketId)
{
    $ticket = \App\Models\Ticket::find($ticketId);
    if (!$ticket) {
        return ['error' => 'Ticket not found'];
    }

    return [
        'user_id' => $this->id,
        'user_sites' => $this->sites->pluck('id')->toArray(),
        'user_companies' => $this->companies->pluck('id')->toArray(),
        'ticket_site_id' => $ticket->site_id,
        'ticket_company_id' => $ticket->site->company_id,
        'can_access_ticket_channel' => $this->sites->contains($ticket->site_id) || $this->companies->contains($ticket->site->company_id),
        'can_access_site_channel' => $this->sites->contains($ticket->site_id),
        'can_access_company_channel' => $this->companies->contains($ticket->site->company_id),
    ];
}
```

Then call it in your controller or tinker:

```php
auth()->user()->debugChannelAccess(123); // Replace 123 with actual ticket ID
```

## Step 7: Check Reverb Logs

Monitor the Reverb server output when users try to connect. You should see:

```
[2024-01-15 10:30:45] Connection established
[2024-01-15 10:30:45] Subscribing to private-ticket.123
[2024-01-15 10:30:45] Subscription authorized for private-ticket.123
```

If you see authorization failures, the issue is in `routes/channels.php`.

## Step 8: Check Laravel Logs

When events are broadcast, check `storage/logs/laravel.log` for:

```
Manual broadcast test triggered from Site view
Site TicketView: Ticket refreshed via WebSocket
Company TicketView: Ticket refreshed via WebSocket
```

## Step 9: Common Issues & Solutions

### Issue: "Echo is not available"
**Solution:** Check that Vite environment variables are set and `npm run dev` is running.

### Issue: WebSocket connection failed
**Solutions:**
- Verify Reverb server is running on correct port
- Check firewall settings
- Ensure `REVERB_HOST` and `REVERB_PORT` are correct

### Issue: Subscription authorization failed
**Solutions:**
- Verify user has access to the site/company
- Check `routes/channels.php` authorization logic
- Use the debug method above to verify user access

### Issue: Events not broadcasting
**Solutions:**
- Check `BROADCAST_CONNECTION=reverb` in `.env`
- Verify events implement `ShouldBroadcast`
- Check that `broadcast()` calls are being executed

### Issue: Components not refreshing
**Solutions:**
- Verify `getListeners()` method returns correct channel names
- Check that listener methods exist and are public
- Ensure ticket/site properties are loaded before `getListeners()` is called

## Step 10: Testing Broadcasting Manually

Test broadcasting manually in tinker:

```php
php artisan tinker

$ticket = \App\Models\Ticket::first();
broadcast(new \App\Events\TicketUpdated($ticket));
```

You should see the event appear in the browser if subscriptions are working.

## Production Checklist

Before deploying to production:

1. ✅ Remove debug buttons and console.log statements
2. ✅ Remove debug logging from components  
3. ✅ Set `REVERB_SCHEME=https` for production
4. ✅ Configure SSL certificates for WebSocket connections
5. ✅ Set up proper Redis scaling if needed
6. ✅ Configure reverse proxy (nginx) for WebSocket connections 
