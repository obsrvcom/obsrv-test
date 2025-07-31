<div class="flex h-full w-full flex-1 flex-col">
    <!-- Navigation -->
    <div class="bg-gray-50 dark:bg-gray-800 border-b border-neutral-200 dark:border-neutral-700 px-4 flex items-center gap-4">
    <flux:badge color="zinc">Company Users</flux:badge>

    <flux:navbar>
            <flux:navbar.item
                :href="route('company.users', ['company' => $company->id])"
                icon="users"
                wire:navigate
            >
                Manage Users
            </flux:navbar.item>
            <flux:navbar.item
                :href="route('company.teams', ['company' => $company->id])"
                :current="true"
                icon="user-group"
                wire:navigate
            >
                Teams
            </flux:navbar.item>
        </flux:navbar>
    </div>
    <div class="p-4 flex flex-col gap-4">

    <!-- Header Section -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-neutral-200 dark:border-neutral-700 p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    Teams
                </h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">
                    Manage teams and their members within the company.
                </p>
            </div>
            <flux:button
                variant="primary"
                icon="plus"
                wire:click="openCreateTeamModal"
            >
                Create Team
            </flux:button>
        </div>
    </div>

    <!-- Teams Content -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-neutral-200 dark:border-neutral-700 flex-1">
        @if(count($teams) > 0)
            <div class="p-6">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    @foreach($teams as $team)
                        <div class="bg-white dark:bg-gray-900 border border-neutral-200 dark:border-neutral-700 rounded-lg p-6">
                            <div class="flex items-start justify-between mb-4">
                                                                <div class="flex items-start gap-3">
                                    <div class="mt-1">
                                        <flux:badge variant="solid" color="{{ $team->color }}" class="w-4 h-4 p-0"></flux:badge>
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $team->name }}</h3>
                                            @if($team->allow_direct_tickets)
                                                <flux:badge variant="solid" color="green" size="sm">Direct Tickets</flux:badge>
                                            @endif
                                        </div>
                                        <p class="text-xs text-gray-400 mt-2">{{ $team->users->count() }} member{{ $team->users->count() !== 1 ? 's' : '' }}</p>
                                    </div>
                                </div>
                                <div class="flex space-x-2">
                                    <flux:button variant="subtle" size="sm" icon="pencil" wire:click="openEditTeamModal({{ $team->id }})">
                                        Edit
                                    </flux:button>
                                    <flux:button variant="danger" size="sm" icon="trash" wire:click="confirmDeleteTeam({{ $team->id }})">
                                        Delete
                                    </flux:button>
                                </div>
                            </div>

                            <!-- Team Members -->
                            @if($team->users->count() > 0)
                                <div class="space-y-2">
                                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300">Members:</h4>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($team->users as $member)
                                            <div class="flex items-center gap-2 bg-gray-50 dark:bg-gray-800 rounded-lg px-3 py-2">
                                                <span class="text-sm text-gray-700 dark:text-gray-300">{{ $member->name }}</span>
                                                                                                    <flux:button
                                                        variant="subtle"
                                                        size="xs"
                                                        icon="x-mark"
                                                        wire:click="confirmRemoveFromTeam({{ $team->id }}, {{ $member->id }})"
                                                    ></flux:button>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <p class="text-sm text-gray-500 dark:text-gray-400">No members in this team yet.</p>
                            @endif

                            <!-- Add Members Button -->
                            <div class="mt-4 pt-4 border-t border-neutral-200 dark:border-neutral-700">
                                <flux:button variant="outline" size="sm" icon="user-plus" wire:click="openAddToTeamModal({{ $team->id }})">
                                    Add Members
                                </flux:button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div class="text-center py-16">
                <div class="mx-auto h-16 w-16 rounded-full bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center">
                    <flux:icon name="user-group" class="h-8 w-8 text-neutral-400" />
                </div>
                <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">No teams yet</h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 max-w-sm mx-auto">
                    Teams help organize users and assign them to specific projects.
                </p>
                <div class="mt-6">
                    <flux:button
                        variant="primary"
                        icon="plus"
                        wire:click="openCreateTeamModal"
                    >
                        Create Your First Team
                    </flux:button>
                </div>
            </div>
        @endif
    </div>
    </div>

    <!-- Team Management Modals -->
    <flux:modal variant="flyout" wire:model.self="showCreateTeamModal" class="md:w-96">
        <div class="space-y-6">
                        <div>
                <flux:heading size="lg">Create Team</flux:heading>
                <flux:text class="mt-2">Create a new team to organize users within your company.</flux:text>
            </div>
                        <form wire:submit="createTeam" class="space-y-6">
                <flux:input label="Team Name" type="text" wire:model="teamName" required autofocus placeholder="e.g. Development Team" />
                @error('teamName')
                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                @enderror

                <div>
                    <flux:radio.group wire:model="teamColor" label="Team Color" variant="pills" class="flex-wrap">
                        @foreach($colorOptions as $colorKey => $colorData)
                            <flux:radio value="{{ $colorKey }}">
                                <flux:badge color="{{ $colorKey }}" size="sm">{{ $colorData['name'] }}</flux:badge>
                            </flux:radio>
                        @endforeach
                    </flux:radio.group>
                    @error('teamColor')
                        <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <flux:checkbox wire:model="allowDirectTickets" label="Allow Direct Ticket Creation" />
                    <flux:text size="sm" class="mt-1 text-gray-600">
                        Allow customers to create tickets directly to this team when opening support requests.
                    </flux:text>
                </div>

                <div class="flex">
                    <flux:spacer />
                    <flux:button variant="ghost" type="button" wire:click="closeCreateTeamModal">Cancel</flux:button>
                    <flux:button variant="primary" type="submit">Create Team</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

    <flux:modal variant="flyout" wire:model.self="showEditTeamModal" class="md:w-96">
        <div class="space-y-6">
                        <div>
                <flux:heading size="lg">Edit Team</flux:heading>
                <flux:text class="mt-2">Update the team's name.</flux:text>
            </div>
                        <form wire:submit="updateTeam" class="space-y-6">
                <flux:input label="Team Name" type="text" wire:model="editTeamName" required placeholder="e.g. Development Team" />
                @error('editTeamName')
                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                @enderror

                <div>
                    <flux:radio.group wire:model="editTeamColor" label="Team Color" variant="pills" class="flex-wrap">
                        @foreach($colorOptions as $colorKey => $colorData)
                            <flux:radio value="{{ $colorKey }}">
                                <flux:badge color="{{ $colorKey }}" size="sm">{{ $colorData['name'] }}</flux:badge>
                            </flux:radio>
                        @endforeach
                    </flux:radio.group>
                    @error('editTeamColor')
                        <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <flux:checkbox wire:model="editAllowDirectTickets" label="Allow Direct Ticket Creation" />
                    <flux:text size="sm" class="mt-1 text-gray-600">
                        Allow customers to create tickets directly to this team when opening support requests.
                    </flux:text>
                </div>

                <div class="flex">
                    <flux:spacer />
                    <flux:button variant="ghost" type="button" wire:click="closeEditTeamModal">Cancel</flux:button>
                    <flux:button variant="primary" type="submit">Update Team</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

    <flux:modal wire:model.self="showDeleteTeamModal" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Delete Team</flux:heading>
                <flux:text class="mt-2">Are you sure you want to delete this team? All team members will be removed from the team. This action cannot be undone.</flux:text>
            </div>
            <div class="flex">
                <flux:spacer />
                <flux:button variant="ghost" type="button" wire:click="$set('showDeleteTeamModal', false)">Cancel</flux:button>
                <flux:button variant="danger" type="button" wire:click="deleteTeam">Delete Team</flux:button>
            </div>
        </div>
    </flux:modal>

    <flux:modal variant="flyout" wire:model.self="showAddToTeamModal" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Add Members to Team</flux:heading>
                <flux:text class="mt-2">Select company users to add to this team.</flux:text>
            </div>
            @if($availableUsers && $availableUsers->count() > 0)
                <form wire:submit="addUsersToTeam" class="space-y-6">
                    <div class="space-y-3">
                        <flux:text class="font-medium">Available Users:</flux:text>
                        <div class="space-y-2 max-h-60 overflow-y-auto">
                            @foreach($availableUsers as $user)
                                <label class="flex items-center space-x-3 p-3 rounded-lg border border-neutral-200 dark:border-neutral-700 hover:bg-neutral-50 dark:hover:bg-neutral-800 cursor-pointer">
                                    <input type="checkbox" wire:model="selectedUserIds" value="{{ $user->id }}" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                    <div class="flex-1">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $user->name }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $user->email }}</div>
                                        @if($user->pivot->job_title)
                                            <div class="text-xs text-gray-400">{{ $user->pivot->job_title }}</div>
                                        @endif
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div class="flex">
                        <flux:spacer />
                        <flux:button variant="ghost" type="button" wire:click="closeAddToTeamModal">Cancel</flux:button>
                        <flux:button variant="primary" type="submit">Add Selected Users</flux:button>
                    </div>
                </form>
            @else
                <div class="text-center py-8">
                    <flux:icon name="users" class="h-12 w-12 text-neutral-400 mx-auto mb-4" />
                    <flux:text class="text-neutral-500">No users available to add to this team.</flux:text>
                    <flux:text size="sm" class="text-neutral-400 mt-1">All company users are already members of this team.</flux:text>
                </div>
                <div class="flex">
                    <flux:spacer />
                    <flux:button variant="primary" type="button" wire:click="closeAddToTeamModal">Close</flux:button>
                </div>
            @endif
        </div>
    </flux:modal>

    <!-- Remove User from Team Confirmation Modal -->
    <flux:modal wire:model.self="showRemoveFromTeamModal" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Remove User from Team</flux:heading>
                <flux:text class="mt-2">
                    Are you sure you want to remove <strong>{{ $userNameForRemoval }}</strong> from the <strong>{{ $teamNameForRemoval }}</strong> team? This action cannot be undone.
                </flux:text>
            </div>
            <div class="flex">
                <flux:spacer />
                <flux:button variant="ghost" type="button" wire:click="$set('showRemoveFromTeamModal', false)">Cancel</flux:button>
                <flux:button variant="danger" type="button" wire:click="removeFromTeam">Remove from Team</flux:button>
            </div>
        </div>
    </flux:modal>

    <script>
        window.addEventListener('flux-toast', event => {
            if (window.Flux && typeof window.Flux.toast === 'function') {
                window.Flux.toast({
                    message: event.detail.message,
                    variant: event.detail.variant || 'default',
                    duration: 3500,
                });
            }
        });
    </script>
</div>
