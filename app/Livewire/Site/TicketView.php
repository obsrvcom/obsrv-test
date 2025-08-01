<?php

namespace App\Livewire\Site;

use App\Models\Ticket;
use App\Models\Site;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Renderless;
use App\Events\TicketUpdated;

class TicketView extends Component
{
    public Ticket $ticket;
    public $newMessage = '';
    public $site;

    protected $listeners = [
        'messageAdded' => 'refreshTimeline',
        'ticketUpdated' => 'refreshTicket',
    ];

                public function getListeners()
    {
        $listeners = $this->listeners;

        if ($this->ticket) {
            $listeners["echo-private:ticket.{$this->ticket->id},ticket.updated"] = 'handleTicketUpdate';
            \Log::info('Site view listening to channel', ['channel' => "ticket.{$this->ticket->id}"]);
        }

        return $listeners;
    }

    public function handleTicketUpdate()
    {
        \Log::info('Site view received ticket update', ['ticket_id' => $this->ticket->id]);
        $this->ticket->refresh();
        $this->ticket->load(['messages.user', 'activities.user']);
    }

    public function mount($ticketId)
    {
        $this->site = request()->route('site');

        // Handle case where route parameter might be string ID
        if (is_string($this->site)) {
            $this->site = Site::find($this->site);
        }

        $this->ticket = Ticket::with(['site', 'createdBy', 'assignedTeam', 'assignedUser'])
            ->findOrFail($ticketId);

        // Ensure ticket belongs to this site
        if ($this->ticket->site_id !== $this->site->id) {
            abort(403, 'You do not have access to this ticket.');
        }


    }

    public function getMessagesProperty()
    {
        return $this->ticket->messages()
            ->with('user')
            ->where('message_type', '!=', 'internal') // Hide internal messages from customers
            ->orderBy('created_at')
            ->get();
    }

    public function getTimelineProperty()
    {
        // Get messages (excluding internal ones)
        $messages = $this->ticket->messages()
            ->with('user')
            ->where('message_type', '!=', 'internal')
            ->get()
            ->map(function ($message) {
                return (object) [
                    'type' => 'message',
                    'data' => $message,
                    'created_at' => $message->created_at,
                    'sort_order' => 2, // Messages come after activities at same timestamp
                ];
            });

        // Get activities
        $activities = $this->ticket->activities()
            ->with('user')
            ->get()
            ->map(function ($activity) {
                return (object) [
                    'type' => 'activity',
                    'data' => $activity,
                    'created_at' => $activity->created_at,
                    'sort_order' => 1, // Activities come before messages at same timestamp
                ];
            });

        // Combine and sort by timestamp first, then by sort_order
        return $messages->merge($activities)->sortBy([
            ['created_at', 'asc'],
            ['sort_order', 'asc']
        ]);
    }

    #[Renderless]
    public function sendMessage()
    {
        $this->validate([
            'newMessage' => 'required|string|min:1|max:2000'
        ]);

        try {
            $this->ticket->messages()->create([
                'user_id' => auth()->id(),
                'message_type' => 'customer',
                'content' => trim($this->newMessage),
            ]);

            $this->newMessage = '';

            // Broadcast for real-time updates to all listeners (including sender)
            broadcast(new TicketUpdated($this->ticket));

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to send message. Please try again.');
        }
    }

    public function refreshTimeline()
    {
        // Refresh the ticket model and its relationships
        $this->ticket->refresh();
        $this->ticket->load(['messages.user', 'activities.user']);
    }

    public function refreshTicket()
    {
        $this->refreshTimeline();
    }





    public function getStatusColorClass($status)
    {
        return match($status) {
            'open' => 'text-green-600 bg-green-100',
            'awaiting_customer' => 'text-amber-600 bg-amber-100',
            'on_hold' => 'text-gray-600 bg-gray-100',
            'closed' => 'text-red-600 bg-red-100',
            default => 'text-gray-600 bg-gray-100'
        };
    }

    public function getStatusLabel($status)
    {
        return match($status) {
            'open' => 'Open',
            'awaiting_customer' => 'Awaiting Your Response',
            'on_hold' => 'On Hold',
            'closed' => 'Closed',
            default => ucfirst($status)
        };
    }

    #[Layout('components.layouts.site')]
    public function render()
    {
        return view('livewire.site.ticket-view');
    }
}
