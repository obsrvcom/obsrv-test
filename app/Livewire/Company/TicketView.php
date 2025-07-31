<?php

namespace App\Livewire\Company;

use App\Models\Company;
use App\Models\Ticket;
use App\Models\Team;
use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\Layout;

class TicketView extends Component
{
    public Company $company;
    public Ticket $ticket;
    public $customerMessage = '';
    public $internalMessage = '';
    public $isLoadingCustomer = false;
    public $isLoadingInternal = false;
    public $showAssignModal = false;
    public $assignToTeam = null;
    public $assignToUser = null;

    protected $listeners = [
        'messageAdded' => '$refresh',
        'ticketUpdated' => '$refresh',
    ];

            public function getListeners()
    {
        $listeners = $this->listeners;

        if ($this->ticket) {
            $listeners["echo-private:ticket.{$this->ticket->id},ticket.updated"] = 'handleTicketUpdate';
            \Log::info('Company view listening to channel', ['channel' => "ticket.{$this->ticket->id}"]);
        }

        return $listeners;
    }

    public function handleTicketUpdate()
    {
        \Log::info('Company view received ticket update', ['ticket_id' => $this->ticket->id]);
        $this->ticket->refresh();
        $this->ticket->load(['messages.user', 'activities.user']);
    }

    public function mount($company = null, $ticket = null)
    {
        // Handle route model binding
        if ($company instanceof Company) {
            $this->company = $company;
        } else {
            $routeCompany = request()->route('company');
            if ($routeCompany instanceof Company) {
                $this->company = $routeCompany;
            } else {
                $this->company = auth()->user()->currentCompanyFromRequest() ?? auth()->user()->currentCompany();
            }
        }

        if ($ticket instanceof Ticket) {
            $this->ticket = $ticket;
        } else {
            $this->ticket = Ticket::findOrFail(request()->route('ticket'));
        }

        // Ensure ticket belongs to this company
        if (!$this->ticket->site || $this->ticket->site->company_id !== $this->company->id) {
            abort(403, 'You do not have access to this ticket.');
        }

        // Set default assignment values
        $this->assignToTeam = $this->ticket->assigned_team_id;
        $this->assignToUser = $this->ticket->assigned_user_id;
    }

    public function getCustomerMessagesProperty()
    {
        return $this->ticket->messages()
            ->with('user')
            ->whereIn('message_type', ['customer', 'company'])
            ->orderBy('created_at')
            ->get();
    }

    public function getInternalMessagesProperty()
    {
        return $this->ticket->messages()
            ->with('user')
            ->where('message_type', 'internal')
            ->orderBy('created_at')
            ->get();
    }

    public function getActivitiesProperty()
    {
        return $this->ticket->activities()
            ->with('user')
            ->latest()
            ->limit(20)
            ->get();
    }

    public function sendCustomerMessage()
    {
        $this->validate([
            'customerMessage' => 'required|string|min:1|max:2000'
        ]);

        $this->isLoadingCustomer = true;

        try {
            $this->ticket->messages()->create([
                'user_id' => auth()->id(),
                'message_type' => 'company',
                'content' => trim($this->customerMessage),
            ]);

            $this->customerMessage = '';
            $this->dispatch('messageAdded');
            $this->dispatch('scrollCustomerToBottom');

            // Also broadcast for real-time updates
            broadcast(new \App\Events\TicketUpdated($this->ticket));

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to send message. Please try again.');
        } finally {
            $this->isLoadingCustomer = false;
        }
    }

    public function sendInternalMessage()
    {
        $this->validate([
            'internalMessage' => 'required|string|min:1|max:2000'
        ]);

        $this->isLoadingInternal = true;

        try {
            $this->ticket->messages()->create([
                'user_id' => auth()->id(),
                'message_type' => 'internal',
                'content' => trim($this->internalMessage),
            ]);

            $this->internalMessage = '';
            $this->dispatch('messageAdded');
            $this->dispatch('scrollInternalToBottom');

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to send internal note. Please try again.');
        } finally {
            $this->isLoadingInternal = false;
        }
    }

    public function updateStatus($status)
    {
        if (in_array($status, ['open', 'awaiting_customer', 'on_hold', 'closed'])) {
            $this->ticket->updateStatus($status, auth()->id());
            $this->dispatch('messageAdded');
        }
    }

    public function openAssignModal()
    {
        $this->showAssignModal = true;
    }

    public function closeAssignModal()
    {
        $this->showAssignModal = false;
        $this->assignToTeam = $this->ticket->assigned_team_id;
        $this->assignToUser = $this->ticket->assigned_user_id;
    }

    public function saveAssignment()
    {
        $this->validate([
            'assignToTeam' => 'nullable|exists:teams,id',
            'assignToUser' => 'nullable|exists:users,id',
        ]);

        if ($this->assignToTeam !== $this->ticket->assigned_team_id) {
            $this->ticket->assignToTeam($this->assignToTeam, auth()->id());
        }

        if ($this->assignToUser !== $this->ticket->assigned_user_id) {
            $this->ticket->assignToUser($this->assignToUser, auth()->id());
        }

        $this->closeAssignModal();
        $this->dispatch('messageAdded');
    }

    public function goBack()
    {
        return $this->redirect(route('company.tickets', ['company' => $this->company->id]));
    }

    public function getTeamsProperty()
    {
        return $this->company->teams()->get();
    }

    public function getCompanyUsersProperty()
    {
        return $this->company->users()->get();
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
            'awaiting_customer' => 'Awaiting Customer',
            'on_hold' => 'On Hold',
            'closed' => 'Closed',
            default => ucfirst($status)
        };
    }



    #[Layout('components.layouts.company')]
    public function render()
    {
        return view('livewire.company.ticket-view');
    }
}
