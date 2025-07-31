<div class="flex flex-col h-screen">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200 px-6 py-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <flux:button wire:click="goBack" variant="ghost" size="sm">
                    <flux:icon.arrow-left class="w-4 h-4" />
                    Back to Tickets
                </flux:button>

                <div class="border-l border-gray-300 h-6"></div>

                <div>
                    <div class="flex items-center gap-3">
                        <h1 class="text-lg font-semibold text-gray-900">{{ $ticket->subject }}</h1>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $this->getStatusColorClass($ticket->status) }}">
                            {{ $this->getStatusLabel($ticket->status) }}
                        </span>
                    </div>
                    <div class="flex items-center gap-2 mt-1 text-sm text-gray-500">
                        <span class="font-mono">{{ $ticket->ticket_number }}</span>
                        <span>•</span>
                        <span>{{ $ticket->site->name }}</span>
                        <span>•</span>
                        <span>Created by {{ $ticket->createdBy->name }}</span>
                        <span>•</span>
                        <span>@preciseTimeAgo($ticket->created_at)</span>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <!-- Status Actions -->
                <flux:dropdown>
                    <flux:button variant="primary" size="sm">
                        Change Status
                        <flux:icon.chevron-down class="w-4 h-4 ml-1" />
                    </flux:button>
                    <flux:menu class="w-48">
                        <flux:menu.item wire:click="updateStatus('open')" icon="check-circle">
                            Open
                        </flux:menu.item>
                        <flux:menu.item wire:click="updateStatus('awaiting_customer')" icon="clock">
                            Awaiting Customer
                        </flux:menu.item>
                        <flux:menu.item wire:click="updateStatus('on_hold')" icon="pause">
                            On Hold
                        </flux:menu.item>
                        <flux:menu.item wire:click="updateStatus('closed')" icon="x-circle">
                            Close Ticket
                        </flux:menu.item>
                    </flux:menu>
                </flux:dropdown>

                <flux:button wire:click="openAssignModal" variant="ghost" size="sm">
                    <flux:icon.user-plus class="w-4 h-4" />
                    Assign
                </flux:button>
            </div>
        </div>
    </div>

    @if(session('error'))
        <div class="bg-red-50 border-l-4 border-red-400 p-4 mx-6 mt-4">
            <p class="text-red-700">{{ session('error') }}</p>
        </div>
    @endif

    <!-- Assignment Info -->
    @if($ticket->assignedTeam || $ticket->assignedUser)
        <div class="bg-blue-50 border-b border-blue-200 px-6 py-3">
            <div class="flex items-center gap-4 text-sm">
                @if($ticket->assignedTeam)
                    <div class="flex items-center gap-2">
                        <flux:icon.user-group class="w-4 h-4 text-blue-600" />
                        <span class="text-blue-900">Assigned to <strong>{{ $ticket->assignedTeam->name }}</strong> team</span>
                    </div>
                @endif
                @if($ticket->assignedUser)
                    <div class="flex items-center gap-2">
                        <flux:icon.user class="w-4 h-4 text-blue-600" />
                        <span class="text-blue-900">Assigned to <strong>{{ $ticket->assignedUser->name }}</strong></span>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- Main Content Area with Dual Columns -->
    <div class="flex-1 flex">
        <!-- Customer Chat Column -->
        <div class="flex-1 flex flex-col border-r border-gray-200">
            <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
                <h2 class="text-sm font-medium text-gray-900">Customer Conversation</h2>
                <p class="text-xs text-gray-500">Visible to customer</p>
            </div>

            <!-- Customer Messages -->
            <div class="flex-1 overflow-y-auto bg-gray-50 px-4 py-4"
                 id="customer-messages-container"
                 x-data="{ scrollToBottom() { this.$el.scrollTop = this.$el.scrollHeight; } }"
                 x-init="scrollToBottom()"
                 @scroll-customer-to-bottom.window="scrollToBottom()">

                <div class="space-y-4">
                    @forelse($this->customerMessages as $message)
                        <div class="flex {{ $message->isCustomerMessage() ? 'justify-end' : 'justify-start' }}">
                            <div class="max-w-xs lg:max-w-md">
                                @if($message->isCustomerMessage())
                                    <!-- Customer Message -->
                                    <div class="bg-blue-600 text-white rounded-lg px-4 py-2">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="text-sm font-medium">{{ $message->user->name }}</span>
                                            <span class="text-xs opacity-75">Customer</span>
                                        </div>
                                        <p class="break-words">{{ $message->content }}</p>
                                        <p class="text-xs mt-1 opacity-75">
                                            {{ $message->created_at->format('M j, Y g:i A') }}
                                        </p>
                                    </div>
                                @else
                                    <!-- Company Message -->
                                    <div class="bg-white border border-gray-200 rounded-lg px-4 py-2">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="text-sm font-medium text-gray-900">{{ $message->user->name }}</span>
                                            <span class="text-xs text-gray-500">Support</span>
                                        </div>
                                        <p class="text-gray-900 break-words">{{ $message->content }}</p>
                                        <p class="text-xs text-gray-500 mt-1">
                                            {{ $message->created_at->format('M j, Y g:i A') }}
                                        </p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8">
                            <flux:icon.chat-bubble-left-ellipsis class="w-12 h-12 mx-auto text-gray-400 mb-4" />
                            <p class="text-gray-500">No customer conversation yet.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Customer Message Input -->
            @if($ticket->status !== 'closed')
                <div class="bg-white border-t border-gray-200 px-4 py-4">
                    <form wire:submit="sendCustomerMessage" class="flex gap-3">
                        <div class="flex-1">
                            <flux:textarea
                                wire:model="customerMessage"
                                placeholder="Reply to customer..."
                                rows="3"
                                class="resize-none"
                                :disabled="$isLoadingCustomer"
                            />
                        </div>
                        <div class="flex-shrink-0 flex flex-col justify-end">
                            <flux:button
                                type="submit"
                                variant="primary"
                                :disabled="$isLoadingCustomer || !$customerMessage"
                            >
                                @if($isLoadingCustomer)
                                    <flux:icon.arrow-path class="w-4 h-4 animate-spin" />
                                @else
                                    <flux:icon.paper-airplane class="w-4 h-4" />
                                @endif
                                Send to Customer
                            </flux:button>
                        </div>
                    </form>
                </div>
            @endif
        </div>

        <!-- Internal Chat Column -->
        <div class="w-1/3 flex flex-col">
            <div class="bg-amber-50 border-b border-amber-200 px-4 py-3">
                <h2 class="text-sm font-medium text-gray-900">Internal Notes</h2>
                <p class="text-xs text-gray-500">Only visible to your team</p>
            </div>

            <!-- Internal Messages -->
            <div class="flex-1 overflow-y-auto bg-amber-50 px-4 py-4"
                 id="internal-messages-container"
                 x-data="{ scrollToBottom() { this.$el.scrollTop = this.$el.scrollHeight; } }"
                 x-init="scrollToBottom()"
                 @scroll-internal-to-bottom.window="scrollToBottom()">

                <div class="space-y-4">
                    @forelse($this->internalMessages as $message)
                        <div class="bg-white border border-amber-200 rounded-lg px-3 py-2">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-sm font-medium text-gray-900">{{ $message->user->name }}</span>
                                <span class="text-xs text-amber-600">Internal</span>
                            </div>
                            <p class="text-gray-900 break-words text-sm">{{ $message->content }}</p>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ $message->created_at->format('M j, Y g:i A') }}
                            </p>
                        </div>
                    @empty
                        <div class="text-center py-8">
                            <flux:icon.chat-bubble-oval-left-ellipsis class="w-8 h-8 mx-auto text-amber-400 mb-2" />
                            <p class="text-amber-700 text-sm">No internal notes yet.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Internal Message Input -->
            <div class="bg-white border-t border-amber-200 px-4 py-4">
                <form wire:submit="sendInternalMessage" class="space-y-3">
                    <flux:textarea
                        wire:model="internalMessage"
                        placeholder="Add internal note..."
                        rows="2"
                        class="resize-none text-sm"
                        :disabled="$isLoadingInternal"
                    />
                    <div class="flex justify-end">
                        <flux:button
                            type="submit"
                            variant="primary"
                            size="sm"
                            :disabled="$isLoadingInternal || !$internalMessage"
                        >
                            @if($isLoadingInternal)
                                <flux:icon.arrow-path class="w-4 h-4 animate-spin" />
                            @else
                                <flux:icon.plus class="w-4 h-4" />
                            @endif
                            Add Note
                        </flux:button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Activity Sidebar -->
        <div class="w-80 bg-gray-50 border-l border-gray-200 flex flex-col">
            <div class="bg-gray-100 border-b border-gray-200 px-4 py-3">
                <h2 class="text-sm font-medium text-gray-900">Activity Log</h2>
            </div>

            <div class="flex-1 overflow-y-auto px-4 py-4">
                <div class="space-y-3">
                    @forelse($this->activities as $activity)
                        <div class="text-sm">
                            <div class="flex items-start gap-2">
                                <div class="w-2 h-2 bg-blue-500 rounded-full mt-2 flex-shrink-0"></div>
                                <div class="flex-1">
                                    <p class="text-gray-900">{{ $activity->description }}</p>
                                    <div class="flex items-center gap-2 mt-1">
                                        @if($activity->user)
                                            <span class="text-xs text-gray-500">{{ $activity->user->name }}</span>
                                        @endif
                                        <span class="text-xs text-gray-400">@preciseTimeAgo($activity->created_at)</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-gray-500 text-sm">No activity yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Assignment Modal -->
    <flux:modal variant="flyout" wire:model.self="showAssignModal" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Assign Ticket</flux:heading>
                <flux:subheading>Assign this ticket to a team or specific user.</flux:subheading>
            </div>

            <div class="space-y-4">
                <flux:select wire:model="assignToTeam" label="Assign to Team" placeholder="Choose a team...">
                    <option value="">No team</option>
                    @foreach($this->teams as $team)
                        <option value="{{ $team->id }}">{{ $team->name }}</option>
                    @endforeach
                </flux:select>

                <flux:select wire:model="assignToUser" label="Assign to User" placeholder="Choose a user...">
                    <option value="">No specific user</option>
                    @foreach($this->companyUsers as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </flux:select>
            </div>

            <div class="flex gap-2 justify-end">
                <flux:button wire:click="closeAssignModal" variant="ghost">Cancel</flux:button>
                <flux:button wire:click="saveAssignment" variant="primary">Save Assignment</flux:button>
            </div>
        </div>
    </flux:modal>
</div>

<script>
    // Auto-scroll to bottom when new messages are added
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('messageAdded', () => {
            setTimeout(() => {
                const customerContainer = document.getElementById('customer-messages-container');
                const internalContainer = document.getElementById('internal-messages-container');

                if (customerContainer) {
                    customerContainer.scrollTop = customerContainer.scrollHeight;
                }
                if (internalContainer) {
                    internalContainer.scrollTop = internalContainer.scrollHeight;
                }
            }, 100);
        });
    });
</script>
