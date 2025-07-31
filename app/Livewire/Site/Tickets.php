<?php

namespace App\Livewire\Site;

use App\Models\Ticket;
use App\Models\Team;
use App\Models\Site;
use Livewire\Component;
use Livewire\Attributes\Layout;

class Tickets extends Component
{
    public $selectedTeamId = null;
    public $subject = '';
    public $description = '';
    public $showNewTicketModal = false;
    public $site;

        protected $listeners = [
        'ticketUpdated' => '$refresh',
    ];

    public function mount()
    {
        $this->site = request()->route('site');

        // Handle case where route parameter might be string ID
        if (is_string($this->site)) {
            $this->site = Site::find($this->site);
        }

        // Site access is already validated by middleware, no need to check again
    }

    public function getTicketsProperty()
    {
        return Ticket::forSite($this->site->id)
            ->with(['assignedTeam', 'messages'])
            ->byLastActivity()
            ->get();
    }

    public function getAvailableTeamsProperty()
    {
        if (!$this->site || !$this->site->company) {
            return collect(); // Return empty collection if site/company not loaded
        }

        return $this->site->company->teams()->allowingDirectTickets()->get();
    }

    public function openNewTicketModal()
    {
        $this->showNewTicketModal = true;
        $this->subject = '';
        $this->description = '';

        // Auto-select the team if there's only one available
        if ($this->availableTeams->count() === 1) {
            $this->selectedTeamId = $this->availableTeams->first()->id;
        } else {
            $this->selectedTeamId = null;
        }
    }

    public function closeNewTicketModal()
    {
        $this->showNewTicketModal = false;
        $this->selectedTeamId = null;
        $this->subject = '';
        $this->description = '';
    }

    public function createTicket()
    {
        $validationRules = [
            'description' => 'required|string|min:10',
        ];

        // Only validate team if there are available teams
        if ($this->availableTeams->count() > 0) {
            $validationRules['selectedTeamId'] = [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    if (!$this->availableTeams->pluck('id')->contains($value)) {
                        $fail('The selected team is not available for direct ticket creation.');
                    }
                }
            ];
        }

        $this->validate($validationRules);

        $ticket = Ticket::create([
            'site_id' => $this->site->id,
            'created_by_user_id' => auth()->id(),
            'assigned_team_id' => $this->availableTeams->count() > 0 ? $this->selectedTeamId : null,
            'subject' => 'Support Request', // Default subject since we removed the field
            'description' => $this->description,
        ]);

        // Add initial message
        $ticket->messages()->create([
            'user_id' => auth()->id(),
            'message_type' => 'customer',
            'content' => $this->description,
        ]);

        $this->closeNewTicketModal();

        session()->flash('message', 'Support ticket created successfully!');

        return $this->redirect(route('site.tickets.view', ['site' => $this->site->id, 'ticketId' => $ticket->id]));
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
            'awaiting_customer' => 'Awaiting Response',
            'on_hold' => 'On Hold',
            'closed' => 'Closed',
            default => ucfirst($status)
        };
    }

    #[Layout('components.layouts.site')]
    public function render()
    {
        return view('livewire.site.tickets');
    }
}
