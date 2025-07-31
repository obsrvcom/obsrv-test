<div>
    <flux:header>
        <flux:heading size="xl">Support Tickets</flux:heading>

        <x-slot:actions>
            <flux:input
                wire:model.live.debounce.300ms="search"
                placeholder="Search tickets..."
                icon="magnifying-glass"
                size="sm"
                class="w-64"
            />
        </x-slot:actions>
    </flux:header>

    <!-- Filters -->
    <div class="bg-white rounded-lg border border-gray-200 p-4 mb-6">
        <div class="flex flex-wrap gap-4">
            <div class="flex items-center gap-2">
                <flux:label>Status:</flux:label>
                <flux:select wire:model.live="statusFilter" size="sm" class="min-w-36">
                    <option value="all">All Statuses</option>
                    <option value="open">Open</option>
                    <option value="awaiting_customer">Awaiting Customer</option>
                    <option value="on_hold">On Hold</option>
                    <option value="closed">Closed</option>
                </flux:select>
            </div>

            <div class="flex items-center gap-2">
                <flux:label>Team:</flux:label>
                <flux:select wire:model.live="teamFilter" size="sm" class="min-w-36">
                    <option value="all">All Teams</option>
                    @foreach($this->teams as $team)
                        <option value="{{ $team->id }}">{{ $team->name }}</option>
                    @endforeach
                </flux:select>
            </div>

            <div class="flex items-center gap-2">
                <flux:label>Assignment:</flux:label>
                <flux:select wire:model.live="assignedFilter" size="sm" class="min-w-36">
                    <option value="all">All Tickets</option>
                    <option value="assigned_to_me">Assigned to Me</option>
                    <option value="my_teams">My Teams</option>
                    <option value="unassigned">Unassigned</option>
                </flux:select>
            </div>
        </div>
    </div>

    <!-- Tickets List -->
    <div class="space-y-4">
        @forelse($this->tickets as $ticket)
            <a href="{{ route('company.tickets.view', ['company' => $company->id, 'ticket' => $ticket->id]) }}"
               wire:navigate
               class="block bg-white rounded-lg border border-gray-200 border-l-4 {{ $this->getPriorityClass($ticket) }} hover:shadow-md transition-shadow">
                <div class="p-6">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <span class="font-mono text-sm font-medium text-gray-900">{{ $ticket->ticket_number }}</span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $this->getStatusColorClass($ticket->status) }}">
                                    {{ $this->getStatusLabel($ticket->status) }}
                                </span>
                                @if($ticket->assignedTeam)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                          style="background-color: {{ $ticket->assignedTeam->color ? 'rgb(' . implode(',', sscanf($ticket->assignedTeam->color, '#%02x%02x%02x')) . ')' : 'rgb(59, 130, 246)' }};
                                                 color: white;">
                                        {{ $ticket->assignedTeam->name }}
                                    </span>
                                @endif
                                @if($ticket->assignedUser)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium text-purple-600 bg-purple-100">
                                        {{ $ticket->assignedUser->name }}
                                    </span>
                                @endif
                            </div>

                            <h3 class="text-lg font-medium text-gray-900 mb-2">{{ $ticket->subject }}</h3>

                            <div class="text-sm text-gray-500 mb-2">
                                <span class="font-medium">{{ $ticket->site->name }}</span> •
                                <span>Created by {{ $ticket->createdBy->name }}</span> •
                                <span>@preciseTimeAgo($ticket->created_at)</span>
                            </div>

                            <div class="flex items-center gap-4 text-sm">
                                <div class="flex items-center gap-1">
                                    <flux:icon.chat-bubble-left-ellipsis class="w-4 h-4 text-gray-400" />
                                    <span class="text-gray-600">{{ $ticket->messages->count() }} message{{ $ticket->messages->count() !== 1 ? 's' : '' }}</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <flux:icon.clock class="w-4 h-4 text-gray-400" />
                                    <span class="text-gray-600">{{ $ticket->time_since_last_response }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex-shrink-0">
                            <flux:button variant="ghost" size="sm">
                                View Ticket
                                <flux:icon.arrow-right class="w-4 h-4 ml-1" />
                            </flux:button>
                        </div>
                    </div>
                </div>
            </a>
        @empty
            <div class="bg-white rounded-lg border border-gray-200 py-16">
                <div class="text-center">
                    @if(!empty($this->search) || $this->statusFilter !== 'all' || $this->teamFilter !== 'all' || $this->assignedFilter !== 'all')
                        <flux:icon.funnel class="w-12 h-12 mx-auto text-gray-400 mb-4" />
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No tickets found</h3>
                        <p class="text-gray-500 mb-6">Try adjusting your filters to see more results.</p>
                        <flux:button wire:click="$set('statusFilter', 'all'); $set('teamFilter', 'all'); $set('assignedFilter', 'all'); $set('search', '')" variant="ghost">
                            Clear Filters
                        </flux:button>
                    @else
                        <flux:icon.ticket class="w-12 h-12 mx-auto text-gray-400 mb-4" />
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No support tickets yet</h3>
                        <p class="text-gray-500">Support tickets will appear here when customers create them.</p>
                    @endif
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($this->tickets->hasPages())
        <div class="mt-6">
            {{ $this->tickets->links() }}
        </div>
    @endif
</div>
