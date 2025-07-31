<?php

namespace App\Livewire\Company;

use Livewire\Component;
use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\MagicLinkMail;
use Illuminate\Support\Str;
use Flux\Flux;

class Users extends Component
{
    public Company $company;

    // User management properties
    public $email = '';
    public $role = 'member';
    public $jobTitle = '';
    public $successMessage = null;
    public $errorMessage = null;
    public $showInviteModal = false;
    public $showRemoveModal = false;
    public $userIdToRemove = null;
    public $showResendModal = false;
    public $userIdToResend = null;
    public $showEditModal = false;
    public $editingUser = null;
    public $editName = '';
    public $editRole = '';
    public $editJobTitle = '';

    protected $rules = [
        'email' => 'required|email',
        'role' => 'required|in:admin,member',
        'jobTitle' => 'nullable|string|max:255',
        'editName' => 'required|string|max:255',
        'editRole' => 'required|in:admin,member',
        'editJobTitle' => 'nullable|string|max:255',
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

    // User management methods
    public function openInviteModal()
    {
        $this->reset(['email', 'role', 'jobTitle', 'successMessage', 'errorMessage']);
        $this->role = 'member'; // Default role
        $this->showInviteModal = true;
    }

    public function closeInviteModal()
    {
        $this->showInviteModal = false;
    }

    public function inviteUser()
    {
        $this->validate([
            'email' => 'required|email',
            'role' => 'required|in:admin,member',
            'jobTitle' => 'nullable|string|max:255',
        ]);

        // Check if user already exists
        $existingUser = User::where('email', $this->email)->first();

        if ($existingUser && $this->company->hasUser($existingUser)) {
            $this->errorMessage = 'User is already a member of this company.';
            return;
        }

        if ($existingUser) {
            // Add existing user to company
            $this->company->users()->attach($existingUser->id, [
                'role' => $this->role,
                'job_title' => $this->jobTitle,
            ]);
        } else {
            // Create new user and send invite
            $user = User::create([
                'name' => $this->email,
                'email' => $this->email,
                'password' => null,
                'email_verified_at' => null,
            ]);

            $this->company->users()->attach($user->id, [
                'role' => $this->role,
                'job_title' => $this->jobTitle,
            ]);

            // Send magic link for registration
            $token = Str::random(64);

            // Store invitation data in cache for redirect handling
            $magicLinkData = [
                'email' => $user->email,
                'remember' => true,
                'company_id' => $this->company->id,
                'is_invitation' => true
            ];
            cache()->put("magic_link_{$token}", $magicLinkData, now()->addMinutes(15));

            Mail::to($user->email)->queue(new MagicLinkMail($token, $this->company, true));
        }

        $this->closeInviteModal();
        Flux::toast(text: 'User invited successfully.', variant: 'success', duration: 3500);
    }

    public function confirmRemoveUser($userId)
    {
        $this->userIdToRemove = $userId;
        $this->showRemoveModal = true;
    }

    public function removeUser()
    {
        if ($this->userIdToRemove) {
            // Remove user from all teams in this company first
            $teams = $this->company->teams;
            foreach ($teams as $team) {
                $team->users()->detach($this->userIdToRemove);
            }

            // Remove user from company
            $this->company->users()->detach($this->userIdToRemove);

            $this->showRemoveModal = false;
            $this->userIdToRemove = null;
            Flux::toast(text: 'User removed successfully.', variant: 'success', duration: 3500);
        }
    }

    public function confirmResendInvitation($userId)
    {
        $this->userIdToResend = $userId;
        $this->showResendModal = true;
    }

    public function resendInvitation()
    {
        if ($this->userIdToResend) {
            $user = User::find($this->userIdToResend);

            if ($user && is_null($user->email_verified_at) && is_null($user->password)) {
                $token = Str::random(64);

                // Store invitation data in cache for redirect handling
                $magicLinkData = [
                    'email' => $user->email,
                    'remember' => true,
                    'company_id' => $this->company->id,
                    'is_invitation' => true
                ];
                cache()->put("magic_link_{$token}", $magicLinkData, now()->addMinutes(15));

                Mail::to($user->email)->queue(new MagicLinkMail($token, $this->company, true));

                $this->showResendModal = false;
                $this->userIdToResend = null;
                Flux::toast(text: 'Invitation resent successfully.', variant: 'success', duration: 3500);
            }
        }
    }

    public function openEditModal($userId)
    {
        $this->editingUser = $this->company->users()->where('user_id', $userId)->first();
        if ($this->editingUser) {
            $this->editName = $this->editingUser->name;
            $this->editRole = $this->editingUser->pivot->role;
            $this->editJobTitle = $this->editingUser->pivot->job_title ?? '';
            $this->showEditModal = true;
        }
    }

    public function closeEditModal()
    {
        $this->showEditModal = false;
        $this->editingUser = null;
        $this->reset(['editName', 'editRole', 'editJobTitle']);
    }

    public function updateUser()
    {
        $isEditingSelf = $this->editingUser && $this->editingUser->id == auth()->id();

        if ($isEditingSelf) {
            // For self-editing, only validate name and job title
            $this->validate([
                'editName' => 'required|string|max:255',
                'editJobTitle' => 'nullable|string|max:255',
            ]);
        } else {
            // For editing others, validate all fields including role
            $this->validate([
                'editName' => 'required|string|max:255',
                'editRole' => 'required|in:admin,member',
                'editJobTitle' => 'nullable|string|max:255',
            ]);
        }

        if (!$this->editingUser) {
            return;
        }

        // Update user name
        $this->editingUser->update(['name' => $this->editName]);

        // Update pivot data
        $pivotData = ['job_title' => $this->editJobTitle];

        // Only update role if not editing self
        if (!$isEditingSelf) {
            $pivotData['role'] = $this->editRole;
        }

        $this->company->users()->updateExistingPivot($this->editingUser->id, $pivotData);

        // Refresh company data
        $this->company->unsetRelation('users');
        $this->company = Company::with('users')->find($this->company->id);

        $this->closeEditModal();
        Flux::toast(text: 'User updated successfully.', variant: 'success', duration: 3500);
    }

    public function render()
    {
        $currentUserId = auth()->id();

        // Load users with team information
        $users = $this->company->users()->withPivot('role', 'job_title')->get()->map(function ($user) use ($currentUserId) {
            $user->is_pending = is_null($user->email_verified_at) && is_null($user->password) && $user->id !== $currentUserId;

            // Load user's teams for this company
            $user->userTeams = $user->teams()->where('company_id', $this->company->id)->get();

            return $user;
        });

        return view('livewire.company.users', [
            'users' => $users,
        ])->layout('components.layouts.company');
    }
}
