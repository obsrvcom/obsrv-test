<?php

namespace App\Livewire\Company;

use App\Models\Company;
use App\Models\Ticket;
use App\Models\Team;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;

class Tickets extends Component
{
    use WithPagination;

    public Company $company;
    public $statusFilter = 'all';
    public $teamFilter = 'all';
    public $assignedFilter = 'all';
    public $search = '';

        protected $listeners = [
        'ticketUpdated' => '$refresh',
    ];

    protected $queryString = [
        'statusFilter' => ['except' => 'all'],
        'teamFilter' => ['except' => 'all'],
        'assignedFilter' => ['except' => 'all'],
        'search' => ['except' => ''],
    ];

    public function mount($company = null)
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
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingTeamFilter()
    {
        $this->resetPage();
    }

    public function updatingAssignedFilter()
    {
        $this->resetPage();
    }

    public function getTicketsProperty()
    {
        $query = Ticket::forCompany($this->company->id)
            ->with(['site', 'createdBy', 'assignedTeam', 'assignedUser', 'messages']);

        // Apply status filter
        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        // Apply team filter
        if ($this->teamFilter !== 'all') {
            $query->where('assigned_team_id', $this->teamFilter);
        }

        // Apply assigned filter
        if ($this->assignedFilter === 'assigned_to_me') {
            $query->where('assigned_user_id', auth()->id());
        } elseif ($this->assignedFilter === 'my_teams') {
            $userTeamIds = auth()->user()->teams()->pluck('teams.id');
            $query->whereIn('assigned_team_id', $userTeamIds);
        } elseif ($this->assignedFilter === 'unassigned') {
            $query->whereNull('assigned_team_id')->whereNull('assigned_user_id');
        }

        // Apply search
        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('ticket_number', 'like', '%' . $this->search . '%')
                  ->orWhere('subject', 'like', '%' . $this->search . '%')
                  ->orWhereHas('site', function ($sq) {
                      $sq->where('name', 'like', '%' . $this->search . '%');
                  })
                  ->orWhereHas('createdBy', function ($uq) {
                      $uq->where('name', 'like', '%' . $this->search . '%');
                  });
            });
        }

        // Order by priority (oldest company response first)
        return $query->byPriority()->paginate(20);
    }

    public function getTeamsProperty()
    {
        return $this->company->teams()->get();
    }

    public function openTicket($ticketId)
    {
        return $this->redirect(route('company.tickets.view', ['company' => $this->company->id, 'ticket' => $ticketId]));
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

    public function getPriorityClass($ticket)
    {
        $priorityScore = $ticket->priority_score;

        if ($priorityScore > 1440) { // > 24 hours
            return 'border-l-red-500';
        } elseif ($priorityScore > 480) { // > 8 hours
            return 'border-l-amber-500';
        } elseif ($priorityScore > 240) { // > 4 hours
            return 'border-l-yellow-500';
        } else {
            return 'border-l-green-500';
        }
    }

    #[Layout('components.layouts.company')]
    public function render()
    {
        return view('livewire.company.tickets');
    }
}
