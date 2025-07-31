<?php

namespace App\Livewire\Site;

use App\Models\Ticket;
use App\Models\Site;
use Livewire\Component;
use Livewire\Attributes\Layout;

class TicketView extends Component
{
    public Ticket $ticket;
    public $newMessage = '';
    public $isLoading = false;
    public $site;

    protected $listeners = ['messageAdded' => '$refresh'];

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

    public function sendMessage()
    {
        $this->validate([
            'newMessage' => 'required|string|min:1|max:2000'
        ]);

        $this->isLoading = true;

        try {
            $this->ticket->messages()->create([
                'user_id' => auth()->id(),
                'message_type' => 'customer',
                'content' => trim($this->newMessage),
            ]);

            $this->newMessage = '';

            // Dispatch event to refresh messages
            $this->dispatch('messageAdded');

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to send message. Please try again.');
        } finally {
            $this->isLoading = false;
        }
    }

    public function goBack()
    {
        return $this->redirect(route('site.tickets', ['site' => $this->site->id]));
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
