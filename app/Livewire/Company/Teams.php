<?php

namespace App\Livewire\Company;

use Livewire\Component;
use App\Models\Company;
use App\Models\Team;
use Flux\Flux;

class Teams extends Component
{
    public Company $company;

    // Team management properties
    public $teamName = '';
    public $teamColor = 'blue';
    public $allowDirectTickets = false;
    public $showCreateTeamModal = false;
    public $showEditTeamModal = false;
    public $editingTeam = null;
    public $editTeamName = '';
    public $editTeamColor = 'blue';
    public $editAllowDirectTickets = false;
    public $showDeleteTeamModal = false;
    public $teamIdToDelete = null;
    public $showAddToTeamModal = false;
    public $selectedTeamId = null;
    public $selectedUserIds = [];

    // Remove user from team confirmation
    public $showRemoveFromTeamModal = false;
    public $teamIdForRemoval = null;
    public $userIdForRemoval = null;
    public $userNameForRemoval = '';
    public $teamNameForRemoval = '';

    protected $rules = [
        'teamName' => 'required|string|max:255',
        'teamColor' => 'required|string|in:red,orange,amber,yellow,lime,green,emerald,teal,cyan,sky,blue,indigo,violet,purple,fuchsia,pink,rose',
        'allowDirectTickets' => 'boolean',
        'editTeamName' => 'required|string|max:255',
        'editTeamColor' => 'required|string|in:red,orange,amber,yellow,lime,green,emerald,teal,cyan,sky,blue,indigo,violet,purple,fuchsia,pink,rose',
        'editAllowDirectTickets' => 'boolean',
    ];

    public function mount($company = null)
    {
        // Handle route model binding
        if ($company instanceof Company) {
            $this->company = $company;
        } else {
            // Fallback to getting company from route or current company
            $routeCompany = request()->route('company');
            if ($routeCompany instanceof Company) {
                $this->company = $routeCompany;
            } else {
                $this->company = auth()->user()->currentCompanyFromRequest() ?? auth()->user()->currentCompany();
            }
        }
    }

    // Team management methods
    public function openCreateTeamModal()
    {
        $this->reset(['teamName', 'allowDirectTickets']);
        $this->teamColor = $this->getNextAvailableColor();
        $this->allowDirectTickets = false;
        $this->showCreateTeamModal = true;
    }

        /**
     * Get the next available color that hasn't been used by existing teams
     */
    private function getNextAvailableColor()
    {
        $availableColors = ['red', 'orange', 'amber', 'yellow', 'lime', 'green', 'emerald', 'teal', 'cyan', 'sky', 'blue', 'indigo', 'violet', 'purple', 'fuchsia', 'pink', 'rose'];

        // Get colors already used by existing teams in this company
        // Filter out null values (for older teams that existed before colors were added)
        $usedColors = $this->company->teams()
            ->whereNotNull('color')
            ->pluck('color')
            ->filter()
            ->toArray();

        // Find the first available color that hasn't been used
        foreach ($availableColors as $color) {
            if (!in_array($color, $usedColors)) {
                return $color;
            }
        }

        // If all colors are used, cycle back to the beginning
        // This ensures we don't just default to blue when we have 17 colors available
        return $availableColors[0];
    }

    public function closeCreateTeamModal()
    {
        $this->showCreateTeamModal = false;
    }

    public function createTeam()
    {
        $this->validate([
            'teamName' => [
                'required',
                'string',
                'max:255',
                'unique:teams,name,NULL,id,company_id,' . $this->company->id
            ],
            'teamColor' => 'required|string|in:red,orange,amber,yellow,lime,green,emerald,teal,cyan,sky,blue,indigo,violet,purple,fuchsia,pink,rose',
            'allowDirectTickets' => 'boolean',
        ], [
            'teamName.unique' => 'A team with this name already exists in your company.',
        ]);

        Team::create([
            'name' => $this->teamName,
            'color' => $this->teamColor,
            'allow_direct_tickets' => $this->allowDirectTickets,
            'company_id' => $this->company->id,
        ]);

        $this->closeCreateTeamModal();
        Flux::toast(text: 'Team created successfully.', variant: 'success', duration: 3500);
    }

    public function openEditTeamModal($teamId)
    {
        $this->editingTeam = Team::find($teamId);
        if ($this->editingTeam) {
            $this->editTeamName = $this->editingTeam->name;
            $this->editTeamColor = $this->editingTeam->color ?? 'blue';
            $this->editAllowDirectTickets = $this->editingTeam->allow_direct_tickets ?? false;
            $this->showEditTeamModal = true;
        }
    }

    public function closeEditTeamModal()
    {
        $this->showEditTeamModal = false;
        $this->editingTeam = null;
        $this->reset(['editTeamName', 'editTeamColor', 'editAllowDirectTickets']);
    }

    public function updateTeam()
    {
        $this->validate([
            'editTeamName' => [
                'required',
                'string',
                'max:255',
                'unique:teams,name,' . $this->editingTeam->id . ',id,company_id,' . $this->company->id
            ],
            'editTeamColor' => 'required|string|in:red,orange,amber,yellow,lime,green,emerald,teal,cyan,sky,blue,indigo,violet,purple,fuchsia,pink,rose',
            'editAllowDirectTickets' => 'boolean',
        ], [
            'editTeamName.unique' => 'A team with this name already exists in your company.',
        ]);

        if ($this->editingTeam) {
            $this->editingTeam->update([
                'name' => $this->editTeamName,
                'color' => $this->editTeamColor,
                'allow_direct_tickets' => $this->editAllowDirectTickets,
            ]);

            $this->closeEditTeamModal();
            Flux::toast(text: 'Team updated successfully.', variant: 'success', duration: 3500);
        }
    }

    public function confirmDeleteTeam($teamId)
    {
        $this->teamIdToDelete = $teamId;
        $this->showDeleteTeamModal = true;
    }

    public function deleteTeam()
    {
        if ($this->teamIdToDelete) {
            $team = Team::find($this->teamIdToDelete);
            if ($team && $team->company_id === $this->company->id) {
                $team->delete();

                $this->showDeleteTeamModal = false;
                $this->teamIdToDelete = null;
                Flux::toast(text: 'Team deleted successfully.', variant: 'success', duration: 3500);
            }
        }
    }

    public function confirmRemoveFromTeam($teamId, $userId)
    {
        $team = Team::find($teamId);
        $user = $this->company->users()->where('user_id', $userId)->first();

        if ($team && $user && $team->company_id === $this->company->id) {
            $this->teamIdForRemoval = $teamId;
            $this->userIdForRemoval = $userId;
            $this->userNameForRemoval = $user->name;
            $this->teamNameForRemoval = $team->name;
            $this->showRemoveFromTeamModal = true;
        }
    }

    public function removeFromTeam()
    {
        if ($this->teamIdForRemoval && $this->userIdForRemoval) {
            $team = Team::find($this->teamIdForRemoval);
            if ($team && $team->company_id === $this->company->id) {
                $team->users()->detach($this->userIdForRemoval);

                $this->showRemoveFromTeamModal = false;
                $this->teamIdForRemoval = null;
                $this->userIdForRemoval = null;
                $this->userNameForRemoval = '';
                $this->teamNameForRemoval = '';

                Flux::toast(text: 'User removed from team.', variant: 'success', duration: 3500);
            }
        }
    }

    public function openAddToTeamModal($teamId)
    {
        $this->selectedTeamId = $teamId;
        $this->selectedUserIds = [];
        $this->showAddToTeamModal = true;
    }

    public function closeAddToTeamModal()
    {
        $this->showAddToTeamModal = false;
        $this->selectedTeamId = null;
        $this->selectedUserIds = [];
    }

    public function addUsersToTeam()
    {
        if ($this->selectedTeamId && count($this->selectedUserIds) > 0) {
            $team = Team::find($this->selectedTeamId);
            if ($team && $team->company_id === $this->company->id) {
                // Only attach users that aren't already in the team
                $existingUserIds = $team->users()->pluck('user_id')->toArray();
                $newUserIds = array_diff($this->selectedUserIds, $existingUserIds);

                if (count($newUserIds) > 0) {
                    $team->users()->attach($newUserIds);
                    Flux::toast(text: count($newUserIds) . ' user(s) added to team.', variant: 'success', duration: 3500);
                } else {
                    Flux::toast(text: 'Selected users are already in this team.', variant: 'warning', duration: 3500);
                }
            }
        }

        $this->closeAddToTeamModal();
    }

    public function render()
    {
        // Load teams
        $teams = $this->company->teams()->with('users')->get();

        // Get available users for adding to teams (company users not in the selected team)
        $availableUsers = collect();
        if ($this->selectedTeamId) {
            $team = Team::find($this->selectedTeamId);
            if ($team) {
                $teamUserIds = $team->users()->pluck('user_id')->toArray();
                $availableUsers = $this->company->users()->whereNotIn('user_id', $teamUserIds)->get();
            }
        }

        $colorOptions = [
            'red' => ['name' => 'Red', 'class' => 'bg-red-500'],
            'orange' => ['name' => 'Orange', 'class' => 'bg-orange-500'],
            'amber' => ['name' => 'Amber', 'class' => 'bg-amber-500'],
            'yellow' => ['name' => 'Yellow', 'class' => 'bg-yellow-500'],
            'lime' => ['name' => 'Lime', 'class' => 'bg-lime-500'],
            'green' => ['name' => 'Green', 'class' => 'bg-green-500'],
            'emerald' => ['name' => 'Emerald', 'class' => 'bg-emerald-500'],
            'teal' => ['name' => 'Teal', 'class' => 'bg-teal-500'],
            'cyan' => ['name' => 'Cyan', 'class' => 'bg-cyan-500'],
            'sky' => ['name' => 'Sky', 'class' => 'bg-sky-500'],
            'blue' => ['name' => 'Blue', 'class' => 'bg-blue-500'],
            'indigo' => ['name' => 'Indigo', 'class' => 'bg-indigo-500'],
            'violet' => ['name' => 'Violet', 'class' => 'bg-violet-500'],
            'purple' => ['name' => 'Purple', 'class' => 'bg-purple-500'],
            'fuchsia' => ['name' => 'Fuchsia', 'class' => 'bg-fuchsia-500'],
            'pink' => ['name' => 'Pink', 'class' => 'bg-pink-500'],
            'rose' => ['name' => 'Rose', 'class' => 'bg-rose-500'],
        ];

        return view('livewire.company.teams', [
            'teams' => $teams,
            'availableUsers' => $availableUsers,
            'colorOptions' => $colorOptions,
        ])->layout('components.layouts.company');
    }
}
