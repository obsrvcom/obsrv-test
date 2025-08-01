<div class="grow flex flex-col" x-data="{
    presenceChannel: null,
        init() {
        if (window.Echo) {
            const channelName = 'ticket-presence.{{ $ticket->id }}';

            try {
                this.presenceChannel = window.Echo.join(channelName)
                .here((users) => {
                    this.$wire.call('setOnlineUsers', users);
                })
                .joining((user) => {
                    // Immediately add the new user to the current list
                    const currentUsers = this.$wire.get('onlineUsers') || [];
                    if (!currentUsers.find(u => u.id === user.id)) {
                        const updatedUsers = [...currentUsers, user];
                        this.$wire.call('setOnlineUsers', updatedUsers);
                    }
                })
                .leaving((user) => {
                    // Delay to allow any open popovers to close before DOM update
                    setTimeout(() => {
                        const currentUsers = this.$wire.get('onlineUsers') || [];
                        const updatedUsers = currentUsers.filter(u => u.id !== user.id);
                        this.$wire.call('setOnlineUsers', updatedUsers);
                    }, 250);
                })
                .error((error) => {
                    console.error('❌ Presence channel error:', error);
                });
            } catch (error) {
                console.error('❌ Failed to join presence channel:', error);
            }
        } else {
            console.error('❌ Echo is not available');
        }

        // Listen for debug events
        this.$wire.on('debug-console', (event) => {
            console.log('🐛 Debug onlineUsers:', event.data);
        });
    },
    destroy() {
        // Clean up presence channel
        if (this.presenceChannel) {
            this.presenceChannel.leave();
        }

        // Clean up regular ticket channel
        if (window.Echo) {
            window.Echo.leaveChannel('private-ticket.{{ $ticket->id }}');
        }
    }
}">
    <!-- Header -->

    @if(session('error'))
        <div class="bg-red-50 border-l-4 border-red-400 p-4 mx-6 mt-4">
            <p class="text-red-700">{{ session('error') }}</p>
        </div>
    @endif

    <!-- Main Content Area with Two Columns -->
    <div class="flex-1 flex h-0">
        <!-- Combined Chat Column -->
        <div class="flex-1 flex flex-col border-r border-gray-200 h-full">
        <div class="bg-white border-b border-gray-200 px-6 py-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">

                            <flux:button icon="arrow-left" href="{{ route('company.tickets', ['company' => $company->id]) }}" wire:navigate variant="ghost" size="sm">Back to Tickets</flux:button>


                <div class="border-l border-gray-300 h-6"></div>

                <div>
                    <div class="flex items-center gap-2 text-sm text-gray-500">
                        <span class="font-mono">Support Ticket #{{ $ticket->ticket_number }}</span>
                        <span>•</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $this->getStatusColorClass($ticket->status) }}">
                            {{ $this->getStatusLabel($ticket->status) }}
                        </span>
                        <span>•</span>

                        <span>@preciseTimeAgo($ticket->created_at)</span>
                    </div>
                </div>
            </div>

            <!-- Online Users Display -->
            <div class="flex items-center gap-2">
                <span class="text-xs text-gray-500">Viewing: ({{ count($onlineUsers) }})</span>
                                @if(app()->environment('local'))
                    <button wire:click="debugOnlineUsers"
                            class="text-xs bg-red-100 text-red-600 px-1 rounded">Debug</button>
                @endif
                                                                              @if(count($onlineUsers) > 0)
                    <flux:avatar.group wire:key="online-users-{{ count($onlineUsers) }}">
                        @foreach(array_slice($onlineUsers, 0, 5) as $index => $user)
                            @if(is_array($user) && isset($user['type'], $user['name'], $user['initials']))
                                <flux:dropdown hover position="bottom" wire:key="user-dropdown-{{ $user['id'] }}">
                                    <flux:avatar
                                        as="button"
                                        size="sm"
                                        circle
                                        name="{{ $user['name'] }}"
                                        initials="{{ $user['initials'] }}"
                                        color="{{ $user['type'] === 'company' ? 'blue' : 'green' }}"
                                    />
                                    <flux:popover class="w-64">
                                        <div class="space-y-3">
                                            <div class="flex items-start gap-3">
                                                <flux:avatar
                                                    size="lg"
                                                    circle
                                                    name="{{ $user['name'] }}"
                                                    initials="{{ $user['initials'] }}"
                                                    color="{{ $user['type'] === 'company' ? 'blue' : 'green' }}"
                                                />
                                                <div class="flex-1 min-w-0">
                                                    <flux:heading size="sm">{{ $user['name'] }}</flux:heading>
                                                    @if($user['type'] === 'company' && !empty($user['job_title']))
                                                        <flux:text class="text-xs text-gray-600">{{ $user['job_title'] }}</flux:text>
                                                    @endif
                                                    <flux:text class="text-xs">{{ $user['email'] }}</flux:text>
                                                    <flux:badge
                                                        size="sm"
                                                        color="{{ $user['type'] === 'company' ? 'blue' : 'green' }}"
                                                        class="mt-1"
                                                    >
                                                        {{ ucfirst($user['type']) }}
                                                    </flux:badge>
                                                </div>
                                            </div>
                                            <flux:text class="text-xs text-gray-500">
                                                Currently viewing this ticket
                                            </flux:text>
                                        </div>
                                    </flux:popover>
                                </flux:dropdown>
                            @else
                                <!-- Debug: show invalid user data -->
                                <flux:avatar
                                    size="sm"
                                    circle
                                    color="red"
                                    initials="?"
                                    tooltip="Invalid user data"
                                    wire:key="invalid-{{ $index }}"
                                />
                            @endif
                        @endforeach
                        @if(count($onlineUsers) > 5)
                            <flux:avatar
                                size="sm"
                                circle
                                color="zinc"
                                tooltip="{{ count($onlineUsers) - 5 }} more users viewing"
                                wire:key="overflow-count"
                            >
                                +{{ count($onlineUsers) - 5 }}
                            </flux:avatar>
                        @endif
                    </flux:avatar.group>
            @endif
            </div>
        </div>
    </div>
            <div class="bg-gray-50 border-b border-gray-200 px-4 py-4">
                <div class="flex items-start justify-between gap-6">


                    <!-- Ticket Actions -->
                    <div class="flex flex-wrap items-center justify-between w-full gap-4">
                        <!-- Site Information -->
                        <div class="flex items-center gap-2 min-w-0 flex-shrink">
                            <span class="text-xs text-gray-500 whitespace-nowrap">Site:</span>
                            <span class="text-sm font-medium text-gray-900 truncate">{{ $ticket->site->name }}</span>
                            <flux:button wire:click="openChangeSiteModal" variant="ghost" size="sm">
                                <flux:icon.pencil class="w-3 h-3" />
                            </flux:button>
                        </div>

                        <!-- Status Actions -->
                        <div class="flex items-center gap-2 flex-shrink-0">
                            <span class="text-xs text-gray-500 whitespace-nowrap">Status:</span>
                            <flux:select
                                wire:model.live="pendingStatusChange"
                                variant="listbox"
                                size="sm"
                                class="min-w-32"
                            >
                                <flux:select.option value="open">
                                    <div class="flex items-center gap-2">
                                        <flux:icon.check-circle class="w-3 h-3 text-green-500" />
                                        Open
                                    </div>
                                </flux:select.option>
                                <flux:select.option value="awaiting_customer">
                                    <div class="flex items-center gap-2">
                                        <flux:icon.clock class="w-3 h-3 text-amber-500" />
                                        Awaiting Customer
                                    </div>
                                </flux:select.option>
                                <flux:select.option value="on_hold">
                                    <div class="flex items-center gap-2">
                                        <flux:icon.pause class="w-3 h-3 text-gray-500" />
                                        On Hold
                                    </div>
                                </flux:select.option>
                                <flux:select.option value="closed">
                                    <div class="flex items-center gap-2">
                                        <flux:icon.x-circle class="w-3 h-3 text-red-500" />
                                        Closed
                                    </div>
                                </flux:select.option>
                            </flux:select>
                        </div>

                        <!-- Team Assignment -->
                        <div class="flex items-center gap-2 flex-shrink-0">
                        <flux:input.group>
                            <flux:input.group.prefix class="font-bold">Team</flux:input.group.prefix>
                            <flux:select
                                wire:model.live="pendingTeamId"
                                variant="listbox"
                                searchable
                                clearable
                                placeholder="Assign team..."
                                size="sm"
                                class="min-w-36"
                            >
                                @foreach($this->teams as $team)
                                    <flux:select.option value="{{ $team->id }}">
                                        <div class="flex items-center gap-2 whitespace-nowrap">
                                            <flux:avatar
                                                circle
                                                size="xs"
                                                name="{{ $team->name }}"
                                                color="{{ $team->color ?? 'blue' }}"
                                            />
                                            {{ $team->name }}
                                        </div>
                                    </flux:select.option>
                                @endforeach
                            </flux:select>
    </flux:input.group>


                        <flux:input.group>
                        <flux:input.group.prefix class="font-bold">User</flux:input.group.prefix>
                            <flux:select
                                wire:model.live="pendingUserId"
                                variant="listbox"
                                searchable
                                clearable
                                placeholder="Assign user..."
                                size="sm"
                                class="min-w-36"
                            >
                                                                @foreach($this->users as $user)
                                    <flux:select.option value="{{ $user->id }}">
                                        <div class="flex items-center gap-2 whitespace-nowrap">
                                            <flux:avatar
                                                circle
                                                size="xs"
                                                name="{{ $user->name }}"
                                                initials="{{ $user->initials() }}"
                                                color="auto"
                                                color:seed="{{ $user->id }}"
                                            />
                                            {{ $user->name }}
                                        </div>
                                    </flux:select.option>
                                @endforeach
                            </flux:select>
                            </flux:input.group>

                        </div>
                    </div>
                </div>
            </div>

            <!-- Combined Messages Timeline -->
            <div class="flex-1 overflow-y-auto bg-gray-50 px-4 py-4 flex flex-col-reverse"
                 id="messages-container"
                 x-data="{ scrollToBottom() { this.$el.scrollTop = this.$el.scrollHeight; } }"
                 @scroll-to-bottom.window="scrollToBottom()">

                                                <div class="space-y-6 flex flex-col-reverse">
                    <!-- Current Drafts (always at bottom) -->
                    @if($this->currentDrafts->isNotEmpty())
                        <div class="space-y-3">
                            @foreach($this->currentDrafts as $draft)
                                <!-- Draft Response -->
                                <div class="w-full">
                                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg px-4 py-3 border-dashed">
                                        <div class="flex items-center justify-between mb-2">
                                            <div class="flex items-center gap-2">
                                                <flux:icon.pencil class="w-4 h-4 text-yellow-600" />
                                                <span class="text-sm font-medium text-gray-900">
                                                    {{ $draft->user->name }} is drafting a customer response
                                                </span>
                                            </div>
                                            @if($draft->user_id === auth()->id())
                                                <div class="flex items-center gap-2">
                                                    @if($messageType !== 'customer')
                                                        <flux:button
                                                            wire:click="setMessageType('customer')"
                                                            variant="ghost"
                                                            size="sm"
                                                            class="text-yellow-600 hover:text-yellow-700"
                                                        >
                                                            <flux:icon.pencil class="w-4 h-4" />
                                                            Continue editing
                                                        </flux:button>
                                                    @endif
                                                    <flux:button
                                                        wire:click="discardDraft"
                                                        variant="ghost"
                                                        size="sm"
                                                        class="text-red-600 hover:text-red-700"
                                                        wire:confirm="Are you sure you want to discard this draft?"
                                                    >
                                                        <flux:icon.trash class="w-4 h-4" />
                                                        Discard
                                                    </flux:button>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="text-xs text-gray-500 mb-2">
                                            Last edited @ {{ $draft->updated_at->format('g:i A') }} - {{ $draft->updated_at->diffForHumans() }}
                                        </div>
                                        <p class="text-gray-700 break-words text-sm italic">{{ $draft->content }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <!-- Historical Timeline with Timestamps -->
                    @forelse($this->combinedTimeline as $group)
                        <div class="space-y-3">
                            <!-- Timestamp Header -->
                            <div class="flex justify-center">
                                <div class="inline-flex items-center px-3 py-1 rounded-full bg-gray-50 border text-xs text-gray-500">
                                    <flux:icon.clock class="w-3 h-3 mr-1.5 text-gray-400" />
                                    @if($group->timestamp->isToday())
                                        {{ $group->timestamp->format('g:i A') }} - {{ $group->timestamp->diffForHumans() }}
                                    @else
                                        {{ $group->timestamp->format('M j, g:i A') }} - {{ $group->timestamp->diffForHumans() }}
                                    @endif
                                </div>
                            </div>

                            <!-- Items in this minute group -->
                            <div class="space-y-3">
                                @foreach($group->items as $item)
                                    @if($item->type === 'message')
                                        @php $message = $item->data; @endphp
                                        @if($message->message_type === 'customer')
                                            <!-- Customer Message -->
                                            <div class="flex justify-start">
                                                <div class="w-1/2">
                                                    <div class="bg-blue-600 text-white rounded-lg px-4 py-3">
                                                        <div class="flex items-center gap-2 mb-2">
                                                            <span class="text-sm font-medium">{{ $message->user->name }}</span>
                                                            <span class="text-xs opacity-75">Customer</span>
                                                        </div>
                                                        <p class="break-words">{{ $message->content }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                                                                @elseif($message->message_type === 'company')
                                            <!-- Company Message -->
                                            <div class="flex justify-end">
                                                <div class="flex items-start gap-3 w-1/2">
                                                    <div class="flex-1">
                                                        <div class="bg-white border border-gray-200 rounded-lg px-4 py-3">
                                                            <div class="flex items-center gap-2 mb-2">
                                                                <span class="text-sm font-medium text-gray-900">{{ $message->user->name }}</span>
                                                                <span class="text-xs text-gray-500">Support</span>
                                                            </div>
                                                            <p class="text-gray-900 break-words">{{ $message->content }}</p>
                                                        </div>
                                                    </div>
                                                    <div class="flex-shrink-0">
                                                        @if($company->avatar)
                                                            <div class="w-8 h-8 rounded-full overflow-hidden">
                                                                <img src="{{ $company->getAvatarUrl() }}"
                                                                     alt="{{ $company->name }}"
                                                                     class="w-full h-full object-cover">
                                                            </div>
                                                        @else
                                                            <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center text-xs font-medium text-white">
                                                                {{ strtoupper(substr($company->name, 0, 2)) }}
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @elseif($message->message_type === 'internal')
                                            <!-- Internal Message -->
                                            <div class="flex justify-start">
                                                <div class="flex items-start gap-3 max-w-xs lg:max-w-md">
                                                    <div class="flex-shrink-0">
                                                        <div class="w-8 h-8 bg-gray-500 rounded-full flex items-center justify-center text-xs font-medium text-white">
                                                            {{ strtoupper($message->user->initials()) }}
                                                        </div>
                                                    </div>
                                                    <div class="flex-1">
                                                        <div class="px-2 py-1">
                                                            <div class="flex items-center gap-2 mb-1">
                                                                <flux:icon.lock-closed class="w-3 h-3 text-gray-500" />
                                                                <span class="text-sm font-medium text-gray-700">{{ $message->user->name }}</span>
                                                            </div>
                                                            <div class="bg-gray-100 rounded-lg px-3 py-2">
                                                                <p class="text-gray-600 break-words text-sm italic whitespace-pre-wrap">{{ $message->content }}</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @elseif($item->type === 'internal_group')
                                        <!-- Grouped Internal Messages -->
                                        <div class="flex justify-start">
                                            <div class="flex items-start gap-3 max-w-xs lg:max-w-md">
                                                <div class="flex-shrink-0">
                                                    <div class="w-8 h-8 bg-gray-500 rounded-full flex items-center justify-center text-xs font-medium text-white">
                                                        {{ strtoupper($item->user->initials()) }}
                                                    </div>
                                                </div>
                                                <div class="flex-1">
                                                    <div class="px-2 py-1">
                                                        <!-- User name header -->
                                                        <div class="flex items-center gap-2 mb-2">
                                                            <flux:icon.lock-closed class="w-3 h-3 text-gray-500" />
                                                            <span class="text-sm font-medium text-gray-700">{{ $item->user->name }}</span>
                                                        </div>
                                                        <!-- Multiple messages -->
                                                        <div class="space-y-2">
                                                            @foreach($item->messages as $message)
                                                                <div class="bg-gray-100 rounded-lg px-3 py-2">
                                                                    <p class="text-gray-600 break-words text-sm italic whitespace-pre-wrap">{{ $message->content }}</p>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @elseif($item->type === 'activity')
                                        @php $activity = $item->data; @endphp
                                        <!-- Activity Log -->
                                        <div class="flex justify-center">
                                            <div class="text-center">
                                                <div class="inline-flex items-center px-3 py-1 rounded-full bg-gray-100 border text-xs text-gray-600">
                                                    <flux:icon.information-circle class="w-3 h-3 mr-1.5 text-gray-400" />
                                                    {{ $activity->description }} by {{ $activity->user->name ?? 'System' }}
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8">
                            <flux:icon.chat-bubble-left-ellipsis class="w-12 h-12 mx-auto text-gray-400 mb-4" />
                            <p class="text-gray-500">No conversation yet.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Message Input Area -->
            @if($ticket->status !== 'closed')
                <div class="bg-white border-t border-gray-200">
                                        <!-- Message Type Toggle -->
                    <div class="px-4 py-3 border-b border-gray-100">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                @if($messageType === 'internal')
                                    <flux:button variant="primary" size="sm" disabled>
                                        <flux:icon.lock-closed class="w-4 h-4 mr-1" />
                                        Internal Chat
                                    </flux:button>
                                    <flux:button wire:click="setMessageType('customer')" variant="outline" size="sm">
                                        <flux:icon.chat-bubble-left-right class="w-4 h-4 mr-1" />
                                        Respond to Customer
                                    </flux:button>
                                @else
                                    <flux:button wire:click="setMessageType('internal')" variant="outline" size="sm">
                                        <flux:icon.lock-closed class="w-4 h-4 mr-1" />
                                        Internal Chat
                                    </flux:button>
                                    <flux:button variant="primary" size="sm" disabled>
                                        <flux:icon.chat-bubble-left-right class="w-4 h-4 mr-1" />
                                        Respond to Customer
                                    </flux:button>
                                @endif
                            </div>

                            <!-- Filter Popover -->
                            <flux:dropdown position="bottom" align="end">
                                <flux:button variant="ghost" size="sm" icon="funnel" icon:class="text-gray-400">
                                    Filter
                                    @if(!empty($filters))
                                        <x-slot name="iconTrailing">
                                            <flux:badge size="sm" class="-mr-1">
                                                {{ count($filters) }}
                                            </flux:badge>
                                        </x-slot>
                                    @endif
                                </flux:button>
                                <flux:popover class="flex flex-col gap-4 w-64">
                                    <div>
                                        <flux:heading size="sm">Filter conversation</flux:heading>
                                        <flux:text size="sm" class="text-gray-500">Choose what to show in the timeline</flux:text>
                                    </div>
                                    <flux:checkbox.group wire:model.live="filters" class="flex flex-col gap-3">
                                        <flux:checkbox value="customer_chat" icon="chat-bubble-left-right" label="Customer Chat" description="Messages between customer and support" />
                                        <flux:checkbox value="internal_chat" icon="lock-closed" label="Internal Chat" description="Internal team conversations" />
                                        <flux:checkbox value="activity_updates" icon="information-circle" label="Activity Updates" description="Status changes and assignments" />
                                    </flux:checkbox.group>
                                    @if(!empty($filters))
                                        <flux:separator variant="subtle" />
                                        <flux:button
                                            variant="subtle"
                                            size="sm"
                                            class="justify-start -m-2 px-2"
                                            wire:click="$set('filters', [])"
                                        >
                                            Clear all filters
                                        </flux:button>
                                    @endif
                                </flux:popover>
                            </flux:dropdown>
                        </div>
                    </div>

                    <!-- Message Input -->
                    <div class="px-4 py-4">
                        @if($messageType === 'customer')
                            <form wire:submit="sendCustomerMessage" class="flex gap-3">
                                <div class="flex-1">
                                    <flux:textarea
                                        wire:model.live.debounce.500ms="customerMessage"
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
                        @else
                            <form wire:submit="sendInternalMessage" class="flex gap-3" x-data>
                                <div class="flex-1">
                                    <flux:textarea
                                        wire:model.live="internalMessage"
                                        placeholder="Add internal chat..."
                                        rows="2"
                                        class="resize-none text-sm"
                                        :disabled="$isLoadingInternal"
                                        @keydown.enter="if (!$event.shiftKey) { $event.preventDefault(); if ($wire.internalMessage.trim() && !$wire.isLoadingInternal) { $wire.sendInternalMessage(); } }"
                                    />
                                </div>
                                <div class="flex-shrink-0 flex flex-col justify-end">
                                    <flux:button
                                        type="submit"
                                        variant="primary"
                                        :disabled="$isLoadingInternal || !$internalMessage"
                                    >
                                        @if($isLoadingInternal)
                                            <flux:icon.arrow-path class="w-4 h-4 animate-spin" />
                                        @else
                                            <flux:icon.plus class="w-4 h-4" />
                                        @endif
                                        Add Internal Chat
                                    </flux:button>
                                </div>
                            </form>
                        @endif
                    </div>
                </div>
            @endif
        </div>

    </div>

    <!-- Change Site Modal -->
    <flux:modal wire:model.self="showChangeSiteModal" variant="flyout" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Change Site</flux:heading>
                <flux:subheading>Move this ticket to a different site.</flux:subheading>
            </div>

            <flux:select wire:model="newSiteId" label="Select Site" placeholder="Choose a site...">
                @foreach($this->availableSites as $site)
                    <option value="{{ $site->id }}" {{ $site->id === $ticket->site_id ? 'selected' : '' }}>
                        {{ $site->name }}
                    </option>
                @endforeach
            </flux:select>

            <div class="flex gap-2 justify-end">
                <flux:button wire:click="closeChangeSiteModal" variant="ghost">Cancel</flux:button>
                <flux:button wire:click="changeSite" variant="primary">Change Site</flux:button>
            </div>
        </div>
    </flux:modal>

    <!-- Status Change Confirmation Modal -->
    <flux:modal wire:model.self="showStatusConfirmModal" variant="flyout" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Confirm Status Change</flux:heading>
                <flux:subheading>Are you sure you want to change the status to "{{ $pendingStatus ? $this->getStatusLabel($pendingStatus) : '' }}"?</flux:subheading>
            </div>

            <div class="flex gap-2 justify-end">
                <flux:button wire:click="closeStatusConfirmModal" variant="ghost">Cancel</flux:button>
                <flux:button wire:click="confirmStatusUpdate" variant="primary">Change Status</flux:button>
            </div>
        </div>
    </flux:modal>

    <!-- On Hold Modal -->
    <flux:modal wire:model.self="showOnHoldModal" variant="flyout" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Put Ticket On Hold</flux:heading>
                <flux:subheading>How long should this ticket remain on hold?</flux:subheading>
            </div>

            <div class="space-y-4">
                <flux:select wire:model="holdDuration" label="Hold Duration" placeholder="Select duration...">
                    <option value="1_hour">1 Hour</option>
                    <option value="2_hours">2 Hours</option>
                    <option value="4_hours">4 Hours</option>
                    <option value="8_hours">8 Hours</option>
                    <option value="1_day">1 Day</option>
                    <option value="2_days">2 Days</option>
                    <option value="1_week">1 Week</option>
                    <option value="custom">Custom</option>
                </flux:select>

                @if($holdDuration === 'custom')
                    <flux:input
                        wire:model="customHoldUntil"
                        type="datetime-local"
                        label="Hold Until"
                        placeholder="Select date and time"
                    />
                @endif

                <flux:textarea
                    wire:model="holdReason"
                    label="Reason (Optional)"
                    placeholder="Why is this ticket being put on hold?"
                    rows="3"
                />
            </div>

            <div class="flex gap-2 justify-end">
                <flux:button wire:click="closeOnHoldModal" variant="ghost">Cancel</flux:button>
                <flux:button wire:click="putOnHold" variant="primary">Put On Hold</flux:button>
            </div>
        </div>
    </flux:modal>

    <!-- Assign Team Modal -->
    <flux:modal wire:model.self="showAssignTeamModal" variant="flyout" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Assign Team</flux:heading>
                <flux:subheading>Assign this ticket to a team.</flux:subheading>
            </div>

            <flux:select wire:model="assignToTeam" label="Select Team" placeholder="Choose a team...">
                <flux:select.option value="">
                    <div class="flex items-center gap-2 whitespace-nowrap">
                        <flux:icon.x-circle class="w-4 h-4 text-gray-400" />
                        No team
                    </div>
                </flux:select.option>
                @foreach($this->teams as $team)
                    <flux:select.option value="{{ $team->id }}">
                        <div class="flex items-center gap-2 whitespace-nowrap">
                            <flux:avatar
                                circle
                                size="xs"
                                name="{{ $team->name }}"
                                color="{{ $team->color ?? 'blue' }}"
                            />
                            {{ $team->name }}
                        </div>
                    </flux:select.option>
                @endforeach
            </flux:select>

            <div class="flex gap-2 justify-end">
                <flux:button wire:click="closeAssignTeamModal" variant="ghost">Cancel</flux:button>
                <flux:button wire:click="confirmTeamAssignment" variant="primary">Assign Team</flux:button>
            </div>
        </div>
    </flux:modal>

    <!-- Assign User Modal -->
    <flux:modal wire:model.self="showAssignUserModal" variant="flyout" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Assign User</flux:heading>
                <flux:subheading>Assign this ticket to a specific user.</flux:subheading>
            </div>

            <flux:select wire:model="assignToUser" label="Select User" placeholder="Choose a user...">
                <flux:select.option value="">
                    <div class="flex items-center gap-2 whitespace-nowrap">
                        <flux:icon.user-minus class="w-4 h-4 text-gray-400" />
                        No specific user
                    </div>
                </flux:select.option>
                @foreach($this->companyUsers as $user)
                    <flux:select.option value="{{ $user->id }}">
                        <div class="flex items-center gap-2 whitespace-nowrap">
                            <flux:avatar
                                circle
                                size="xs"
                                name="{{ $user->name }}"
                                initials="{{ $user->initials() }}"
                                color="auto"
                                color:seed="{{ $user->id }}"
                            />
                            <div class="flex flex-col min-w-0">
                                <span class="font-medium">{{ $user->name }}</span>
                                @if($user->pivot && $user->pivot->job_title)
                                    <span class="text-xs text-gray-500">{{ $user->pivot->job_title }}</span>
                                @endif
                            </div>
                        </div>
                    </flux:select.option>
                @endforeach
            </flux:select>

            <div class="flex gap-2 justify-end">
                <flux:button wire:click="closeAssignUserModal" variant="ghost">Cancel</flux:button>
                <flux:button wire:click="confirmUserAssignment" variant="primary">Assign User</flux:button>
            </div>
        </div>
    </flux:modal>

    <!-- Unassign Confirmation Modals -->
    <flux:modal wire:model.self="showUnassignTeamModal" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Remove Team Assignment</flux:heading>
                <flux:subheading>Are you sure you want to remove the team assignment from this ticket? The ticket will no longer be assigned to the "{{ $ticket->assignedTeam?->name }}" team.</flux:subheading>
            </div>

            <div class="flex gap-2 justify-end">
                <flux:button wire:click="closeUnassignTeamModal" variant="ghost">Cancel</flux:button>
                <flux:button wire:click="unassignTeam" variant="primary">Remove Assignment</flux:button>
            </div>
        </div>
    </flux:modal>

    <flux:modal wire:model.self="showUnassignUserModal" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Unassign User</flux:heading>
                <flux:subheading>Are you sure you want to unassign this ticket from "{{ $ticket->assignedUser?->name }}"?</flux:subheading>
            </div>

            <div class="flex gap-2 justify-end">
                <flux:button wire:click="closeUnassignUserModal" variant="ghost">Cancel</flux:button>
                <flux:button wire:click="unassignUser" variant="primary">Unassign User</flux:button>
            </div>
        </div>
    </flux:modal>

    <!-- Assignment Confirmation Modals -->
    <flux:modal wire:model.self="showConfirmTeamAssignModal" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">
                    @if($ticket->assignedTeam && $assignToTeam && $assignToTeam != $ticket->assigned_team_id)
                        Reassign Team
                    @else
                        Assign Team
                    @endif
                </flux:heading>
                <flux:subheading>
                    @if($ticket->assignedTeam && $assignToTeam && $assignToTeam != $ticket->assigned_team_id)
                        @php $newTeam = $this->teams->firstWhere('id', $assignToTeam); @endphp
                        Reassign this ticket from "{{ $ticket->assignedTeam->name }}" to "{{ $newTeam?->name }}"?
                    @elseif($assignToTeam)
                        @php $newTeam = $this->teams->firstWhere('id', $assignToTeam); @endphp
                        Assign this ticket to "{{ $newTeam?->name }}"?
                    @endif
                </flux:subheading>
            </div>

            <div class="flex gap-2 justify-end">
                <flux:button wire:click="closeConfirmTeamAssignModal" variant="ghost">Cancel</flux:button>
                <flux:button wire:click="executeTeamAssignment" variant="primary">
                    @if($ticket->assignedTeam && $assignToTeam && $assignToTeam != $ticket->assigned_team_id)
                        Reassign Team
                    @else
                        Assign Team
                    @endif
                </flux:button>
            </div>
        </div>
    </flux:modal>

    <flux:modal wire:model.self="showConfirmUserAssignModal" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">
                    @if($ticket->assignedUser && $assignToUser && $assignToUser != $ticket->assigned_user_id)
                        Reassign User
                    @else
                        Assign User
                    @endif
                </flux:heading>
                <flux:subheading>
                    @if($ticket->assignedUser && $assignToUser && $assignToUser != $ticket->assigned_user_id)
                        @php $newUser = $this->users->firstWhere('id', $assignToUser); @endphp
                        Reassign this ticket from "{{ $ticket->assignedUser->name }}" to "{{ $newUser?->name }}"?
                    @elseif($assignToUser)
                        @php $newUser = $this->users->firstWhere('id', $assignToUser); @endphp
                        Assign this ticket to "{{ $newUser?->name }}"?
                    @endif
                </flux:subheading>
            </div>

            <div class="flex gap-2 justify-end">
                <flux:button wire:click="closeConfirmUserAssignModal" variant="ghost">Cancel</flux:button>
                <flux:button wire:click="executeUserAssignment" variant="primary">
                    @if($ticket->assignedUser && $assignToUser && $assignToUser != $ticket->assigned_user_id)
                        Reassign User
                    @else
                        Assign User
                    @endif
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>

<script>
    // Auto-scroll to bottom when new messages are added
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('messageAdded', () => {
            setTimeout(() => {
                const messagesContainer = document.getElementById('messages-container');
                if (messagesContainer) {
                    messagesContainer.scrollTop = messagesContainer.scrollHeight;
                }
            }, 100);
        });
    });


</script>
