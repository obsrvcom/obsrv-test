<div class="grow p-6 flex flex-col gap-y-4">


    @if(session('message'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-md mb-6">
            {{ session('message') }}
        </div>
    @endif

    <flux:callout icon="shield-check" color="orange" class="mx-0" inline>
        <flux:callout.heading>Do you need assistance?</flux:callout.heading>
        <flux:callout.text>Access your existing support tickets below or start a a new one</flux:callout.text>
        <x-slot name="actions" class="@md:h-full m-0!">
            <flux:button wire:click="openNewTicketModal" variant="primary">Start a new ticket</flux:button>
        </x-slot>
    </flux:callout>

    @if($this->tickets->count() > 0)
        <!-- Tickets Table -->
        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
            <div class="px-6 py-3 bg-gray-50 border-b border-gray-200">
                <div class="grid grid-cols-12 gap-4 text-xs font-medium text-gray-500 uppercase tracking-wider">
                    <div class="col-span-2">Ticket</div>
                    <div class="col-span-2">Status</div>
                    <div class="col-span-2">Team</div>
                    <div class="col-span-4">Subject</div>
                    <div class="col-span-1">Messages</div>
                    <div class="col-span-1">Action</div>
                </div>
            </div>

            <div class="divide-y divide-gray-200">
                @foreach($this->tickets as $ticket)
                    <div class="px-6 py-4 hover:bg-gray-50 transition-colors cursor-pointer grid grid-cols-12 gap-4 items-center"
                         wire:click="openTicket({{ $ticket->id }})">

                        <!-- Ticket Number -->
                        <div class="col-span-2">
                            <span class="font-mono text-sm font-medium text-gray-900">{{ $ticket->ticket_number }}</span>
                        </div>

                        <!-- Status -->
                        <div class="col-span-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $this->getStatusColorClass($ticket->status) }}">
                                {{ $this->getStatusLabel($ticket->status) }}
                            </span>
                        </div>

                        <!-- Team -->
                        <div class="col-span-2">
                            @if($ticket->assignedTeam)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium text-blue-600 bg-blue-100">
                                    {{ $ticket->assignedTeam->name }}
                                </span>
                            @else
                                <span class="text-sm text-gray-500">Unassigned</span>
                            @endif
                        </div>

                        <!-- Subject -->
                        <div class="col-span-4">
                            <div class="text-sm font-medium text-gray-900 truncate">{{ $ticket->subject }}</div>
                            <div class="text-xs text-gray-500">
                                Last activity @preciseTimeAgo($ticket->messages->last()?->created_at ?? $ticket->created_at)
                            </div>
                        </div>

                        <!-- Message Count -->
                        <div class="col-span-1">
                            <div class="flex items-center">
                                <flux:icon.chat-bubble-left-ellipsis class="w-4 h-4 text-gray-400 mr-1" />
                                <span class="text-sm text-gray-600">{{ $ticket->messages->count() }}</span>
                            </div>
                        </div>

                        <!-- Action -->
                        <div class="col-span-1">
                            <flux:button variant="ghost" size="sm" class="w-full">
                                Open
                            </flux:button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <!-- Empty State -->
        <div class="bg-white rounded-lg border border-gray-200 py-16">
            <div class="text-center">
                <flux:icon.chat-bubble-left-ellipsis class="w-12 h-12 mx-auto text-gray-400 mb-4" />
                <h3 class="text-lg font-medium text-gray-900 mb-2">No support tickets yet</h3>
                <p class="text-gray-500">Use the "Get Support" button above to create your first ticket.</p>
            </div>
        </div>
    @endif

    <!-- New Ticket Modal -->
    <flux:modal variant="flyout" wire:model.self="showNewTicketModal" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Create Support Ticket</flux:heading>
                <flux:subheading>Describe your issue and we'll help you resolve it.</flux:subheading>
            </div>

            <div class="space-y-6">
                @if($this->availableTeams->count() > 0)
                    <flux:select wire:model="selectedTeamId" label="Support Team" placeholder="Choose a team...">
                        @foreach($this->availableTeams as $team)
                            <option value="{{ $team->id }}">{{ $team->name }}</option>
                        @endforeach
                    </flux:select>
                @endif

                <flux:textarea
                    wire:model="description"
                    label="Describe your issue"
                    placeholder="Please provide detailed information about your issue..."
                    rows="6"
                />
            </div>

            <div class="flex gap-2 justify-end">
                <flux:button wire:click="closeNewTicketModal" variant="ghost">Cancel</flux:button>
                <flux:button wire:click="createTicket" variant="primary">Create Ticket</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
