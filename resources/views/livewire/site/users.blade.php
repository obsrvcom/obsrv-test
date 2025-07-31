@php $currentUserId = auth()->id(); @endphp
<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    <!-- Header Section -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-neutral-200 dark:border-neutral-700 p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    Site Users
                </h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">
                    Manage which users have access to this site.
                </p>
            </div>
            <flux:button
                variant="primary"
                icon="user-plus"
                wire:click="openInviteModal"
            >
                Invite User
            </flux:button>
        </div>
    </div>

    <!-- Users Content -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-neutral-200 dark:border-neutral-700 flex-1">
        @if(count($users) > 0)
            <div class="overflow-hidden">
                <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
                    <thead class="bg-neutral-50 dark:bg-neutral-900">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                                Name
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                                Email
                            </th>
                            <th class="px-6 py-4 text-right text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-neutral-200 dark:divide-neutral-700">
                        @foreach($users as $user)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white flex items-center gap-2">
                                        {{ $user->name }}
                                        @if($user->is_pending)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200 ml-2">
                                                Pending Invitation
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $user->email }}
                                        @if($user->id === $currentUserId)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 ml-2">
                                                You
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    @if($user->is_pending)
                                        <flux:button variant="subtle" size="sm" icon="arrow-path" wire:click="confirmResendInvitation({{ $user->id }})">
                                            Re-send Invitation
                                        </flux:button>
                                    @endif
                                    @if($user->id !== $currentUserId)
                                        <flux:button variant="danger" size="sm" icon="trash" wire:click="confirmRemoveUser({{ $user->id }})">
                                            Remove
                                        </flux:button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-16">
                <div class="mx-auto h-16 w-16 rounded-full bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center">
                    <flux:icon name="users" class="h-8 w-8 text-neutral-400" />
                </div>
                <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">No users yet</h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 max-w-sm mx-auto">
                    Get started by inviting users to this site.
                </p>
                <div class="mt-6">
                    <flux:button
                        variant="primary"
                        icon="user-plus"
                        wire:click="openInviteModal"
                    >
                        Invite Your First User
                    </flux:button>
                </div>
            </div>
        @endif
    </div>

    <flux:modal variant="flyout" wire:model.self="showInviteModal" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Invite User to Site</flux:heading>
                <flux:text class="mt-2">Enter the email address to invite a user to this site.</flux:text>
            </div>
            @if($errorMessage)
                <div class="mb-2 p-3 rounded bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-100">
                    {{ $errorMessage }}
                </div>
            @endif
            <form wire:submit.prevent="inviteUser" class="space-y-6">
                <flux:input label="Email address" type="email" wire:model.defer="email" required autofocus autocomplete="email" placeholder="user@example.com" />
                <div class="flex">
                    <flux:spacer />
                    <flux:button variant="ghost" type="button" wire:click="closeInviteModal">Cancel</flux:button>
                    <flux:button variant="primary" type="submit">Send Invite</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

    <flux:modal wire:model.self="showRemoveModal" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Remove User Access</flux:heading>
                <flux:text class="mt-2">Are you sure you want to remove this user's access to the site? This action cannot be undone.</flux:text>
            </div>
            <div class="flex">
                <flux:spacer />
                <flux:button variant="ghost" type="button" wire:click="cancelRemoveUser">Cancel</flux:button>
                <flux:button variant="danger" type="button" wire:click="removeUser">Remove</flux:button>
            </div>
        </div>
    </flux:modal>

    <flux:modal wire:model.self="showResendModal" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Re-send Invitation</flux:heading>
                <flux:text class="mt-2">Are you sure you want to re-send the invitation to this user?</flux:text>
            </div>
            <div class="flex">
                <flux:spacer />
                <flux:button variant="ghost" type="button" wire:click="cancelResendInvitation">Cancel</flux:button>
                <flux:button variant="primary" type="button" wire:click="resendInvitation">Re-send</flux:button>
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
