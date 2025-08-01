<div class="grow flex flex-col bg-gray-50" x-data="{
    presenceChannel: null,
        init() {
        // Join presence channel for presence tracking (UI handled by company view)
        if (window.Echo) {
            const channelName = 'ticket-presence.{{ $ticket->id }}';

            this.presenceChannel = window.Echo.join(channelName)
                .here((users) => {
                    // Site view participates in presence but doesn't display UI
                })
                .joining((user) => {
                    // User joined - company view will handle display updates
                })
                .leaving((user) => {
                    // User left - company view will handle display updates
                })
                .error((error) => {
                    console.error('❌ Presence channel error:', error);
                });
        }
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
    <!-- Simple header with back button and status -->
    <div class="bg-white border-b border-gray-200 px-4 sm:px-6 py-3 flex-shrink-0">
        <div class="flex items-center justify-between">
                            <flux:button href="{{ route('site.tickets', ['site' => $site->id]) }}" wire:navigate variant="ghost" size="sm">
                    <flux:icon.arrow-left class="w-4 h-4" />
                    Back to Support
                </flux:button>



            <div class="flex items-center gap-3">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $this->getStatusColorClass($ticket->status) }}">
                    {{ $this->getStatusLabel($ticket->status) }}
                </span>
                @if($ticket->assignedTeam)
                    <span class="text-sm text-gray-500">{{ $ticket->assignedTeam->name }}</span>
                @endif
            </div>
        </div>
    </div>

    @if(session('error'))
        <div class="bg-red-50 border-l-4 border-red-400 p-4 mx-4 sm:mx-6 mt-4 flex-shrink-0">
            <p class="text-red-700">{{ session('error') }}</p>
        </div>
    @endif



    <!-- Timeline Container - CSS Auto-scroll from bottom -->
    <div class="flex-1 flex flex-col-reverse overflow-y-auto px-4 sm:px-6 py-4">
        <!-- Timeline items in reverse order (newest at bottom) -->
        <div class="flex flex-col space-y-4 max-w-4xl mx-auto w-full">
            @forelse($this->timeline as $item)
                @if($item->type === 'message')
                    @php $message = $item->data; @endphp
                    <div class="flex {{ $message->isCustomerMessage() ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-xs sm:max-w-sm md:max-w-md lg:max-w-lg xl:max-w-2xl">
                            @if($message->isCustomerMessage())
                                <!-- Customer Message -->
                                <div class="bg-blue-600 text-white rounded-lg px-4 py-3">
                                    <p class="break-words">{{ $message->content }}</p>
                                    <p class="text-xs mt-2 opacity-75">
                                        {{ $message->created_at->format('M j, Y g:i A') }}
                                    </p>
                                </div>
                            @else
                                <!-- Company Message -->
                                <div class="bg-white border border-gray-200 rounded-lg px-4 py-3 shadow-sm">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="text-sm font-medium text-gray-900">
                                            {{ $message->user->name }}
                                        </span>
                                        @if($ticket->assignedTeam)
                                            <span class="text-xs text-gray-500">
                                                {{ $ticket->assignedTeam->name }}
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-gray-900 break-words">{{ $message->content }}</p>
                                    <p class="text-xs text-gray-500 mt-2">
                                        {{ $message->created_at->format('M j, Y g:i A') }}
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>
                @elseif($item->type === 'activity')
                    @php $activity = $item->data; @endphp
                    <!-- Activity Log - Discrete one-line message -->
                    <div class="flex justify-center">
                        <div class="text-center">
                            <div class="inline-flex items-center px-3 py-1 rounded-full bg-gray-100 border text-xs text-gray-600">
                                <flux:icon.clock class="w-3 h-3 mr-1.5 text-gray-400" />
                                {{ $activity->description }} by {{ $activity->user->name ?? 'System' }} - {{ $activity->created_at->diffForHumans() }} @ {{ $activity->created_at->format('g:i A') }}
                            </div>
                        </div>
                    </div>
                @endif
            @empty
                <!-- Empty state at the "top" which will appear at bottom due to flex-col-reverse -->
                <div class="text-center py-8">
                    <flux:icon.chat-bubble-left-ellipsis class="w-12 h-12 mx-auto text-gray-400 mb-4" />
                    <p class="text-gray-500">Start the conversation by sending a message.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Message Input - Fixed at bottom -->
    <div class="flex-shrink-0 bg-white border-t border-gray-200">
        @if($ticket->status !== 'closed')
            <div class="px-4 sm:px-6 py-4">
                <div class="max-w-4xl mx-auto">
                    <form wire:submit.prevent="sendMessage">
                        <div class="flex gap-3">
                            <div class="flex-1">
                                <flux:textarea
                                    wire:model="newMessage"
                                    placeholder="Type your message..."
                                    rows="3"
                                    class="resize-none w-full"
                                    wire:loading.attr="disabled"
                                    @keydown.ctrl.enter="$wire.sendMessage()"
                                />
                            </div>
                            <div class="flex-shrink-0 flex flex-col justify-end">
                                <flux:button
                                    type="submit"
                                    variant="primary"
                                    loading="sendMessage"
                                    class="mb-1"
                                >
                                    <flux:icon.paper-airplane class="w-4 h-4" />
                                    Send
                                </flux:button>
                                <p class="text-xs text-gray-500 text-center">Ctrl+Enter</p>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        @else
            <div class="px-4 sm:px-6 py-4 bg-gray-50">
                <div class="text-center max-w-4xl mx-auto">
                    <p class="text-gray-500 text-sm">This ticket has been closed. No new messages can be sent.</p>
                </div>
            </div>
        @endif
    </div>
</div>


