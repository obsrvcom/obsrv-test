<div class="flex h-full w-full flex-1 flex-col py-2">
    <!-- Navigation -->
    <div class="px-6 py-3 flex items-center justify-between">
        <flux:navbar>
            <flux:navbar.item
                :href="route('company.sites', ['company' => $company->id])"
                :current="request()->routeIs('company.sites')"
                icon="building-office"
                wire:navigate
            >
                Manage Sites
            </flux:navbar.item>
            <flux:navbar.item
                :href="route('company.sites.groups', ['company' => $company->id])"
                :current="request()->routeIs('company.sites.groups')"
                icon="folder"
                wire:navigate
            >
                Groups
            </flux:navbar.item>
        </flux:navbar>

        <flux:button
            variant="primary"
            icon="plus"
            size="sm"
            wire:click="openCreateModal"
        >
            Add Site
        </flux:button>
    </div>
    <div class="p-4 flex flex-col gap-4">

    <!-- Sites Content -->
    <div class="bg-white dark:bg-gray-800 rounded-l border border-neutral-200 dark:border-neutral-700 flex-1">
        @if(count($sites) > 0)
            <div class="overflow-hidden">
                <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
                    <thead class="bg-neutral-50 dark:bg-neutral-900">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                                Site Name
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                                Address
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                                Groups
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                                Created
                            </th>
                            <th class="px-6 py-4 text-right text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-neutral-200 dark:divide-neutral-700">
                        @foreach($sites as $site)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                                                <flux:icon name="building-office" class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ $site->name }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-600 dark:text-gray-400 max-w-md">
                                        {{ $site->address ? Str::limit($site->address, 120) : '-' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-600 dark:text-gray-400">
                                        @if($site->siteGroups && count($site->siteGroups) > 0)
                                            <div class="flex flex-wrap gap-1">
                                                @foreach($site->siteGroups as $group)
                                                    <flux:badge color="{{ $group->color ?? 'blue' }}" size="sm">{{ $group->name }}</flux:badge>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-gray-400">No groups</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-600 dark:text-gray-400">
                                        {{ $site->created_at->format('M j, Y') }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <div class="flex justify-end space-x-2">
                                        <flux:button variant="primary" size="sm" icon="eye" :href="route('company.sites.view', ['company' => $company->id, 'site' => $site->id])" wire:navigate>
                                            View
                                        </flux:button>
                                        <flux:button variant="subtle" size="sm" icon="pencil" wire:click="openEditModal({{ $site->id }})">
                                            Edit
                                        </flux:button>
                                        <flux:button variant="danger" size="sm" icon="trash" wire:click="confirmDeleteSite({{ $site->id }})">
                                            Delete
                                        </flux:button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-16">
                <div class="mx-auto h-16 w-16 rounded-full bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center">
                    <flux:icon name="building-office" class="h-8 w-8 text-neutral-400" />
                </div>
                <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">No sites yet</h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 max-w-sm mx-auto">
                    Get started by creating your first site to track your business locations.
                </p>
                <div class="mt-6">
                    <flux:button
                        variant="primary"
                        icon="plus"
                        wire:click="openCreateModal"
                    >
                        Add Your First Site
                    </flux:button>
                </div>
            </div>
        @endif
    </div>

        <!-- Create Site Modal -->
    <flux:modal variant="flyout" wire:model.self="showCreateModal" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Add New Site</flux:heading>
                <flux:text class="mt-2">Add a new business location or site to your company.</flux:text>
            </div>
            @if($errorMessage)
                <div class="mb-2 p-3 rounded bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-100">
                    {{ $errorMessage }}
                </div>
            @endif
            <form wire:submit="createSite" class="space-y-6">
                <flux:input label="Site Name" wire:model="name" required placeholder="Enter site name" />
                <flux:textarea label="Site Address (Optional)" wire:model="address" placeholder="Enter full address" rows="3" />
                <div class="flex">
                    <flux:spacer />
                    <flux:button variant="ghost" type="button" wire:click="closeCreateModal">Cancel</flux:button>
                    <flux:button variant="primary" type="submit">Create Site</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

        <!-- Edit Site Modal -->
    <flux:modal variant="flyout" wire:model.self="showEditModal" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Edit Site</flux:heading>
                <flux:text class="mt-2">Update the site information.</flux:text>
            </div>
            @if($errorMessage)
                <div class="mb-2 p-3 rounded bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-100">
                    {{ $errorMessage }}
                </div>
            @endif
            <form wire:submit="updateSite" class="space-y-6">
                <flux:input label="Site Name" wire:model="name" required placeholder="Enter site name" />
                <flux:textarea label="Site Address (Optional)" wire:model="address" placeholder="Enter full address" rows="3" />

                <!-- Site Groups Selection -->
                @if(count($siteGroups) > 0)
                    <div class="space-y-3">
                        <flux:text class="font-medium">Site Groups:</flux:text>
                        <div class="space-y-2 max-h-40 overflow-y-auto border border-neutral-200 dark:border-neutral-700 rounded-lg p-3">
                            @foreach($siteGroups as $group)
                                <label class="flex items-center space-x-3 cursor-pointer">
                                    <input
                                        type="checkbox"
                                        wire:model="selectedGroupIds"
                                        value="{{ $group->id }}"
                                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                    >
                                    <flux:badge variant="solid" color="{{ $group->color ?? 'blue' }}" class="w-3 h-3 p-0"></flux:badge>
                                    <div class="flex-1">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $group->name }}</div>
                                        @if($group->description)
                                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ Str::limit($group->description, 50) }}</div>
                                        @endif
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="flex">
                    <flux:spacer />
                    <flux:button variant="ghost" type="button" wire:click="closeEditModal">Cancel</flux:button>
                    <flux:button variant="primary" type="submit">Update Site</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

        <!-- Delete Site Modal -->
    <flux:modal wire:model.self="showDeleteModal" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Delete Site</flux:heading>
                <flux:text class="mt-2">Are you sure you want to delete this site? This action cannot be undone.</flux:text>
            </div>
            @if($deletingSite)
                <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                    <div class="flex">
                        <flux:icon name="exclamation-triangle" class="h-5 w-5 text-red-400" />
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800 dark:text-red-200">
                                Delete Site: {{ $deletingSite->name }}
                            </h3>
                            <div class="mt-2 text-sm text-red-700 dark:text-red-300">
                                <p>This will permanently delete the site and all associated data.</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            <div class="flex">
                <flux:spacer />
                <flux:button variant="ghost" type="button" wire:click="closeDeleteModal">Cancel</flux:button>
                <flux:button variant="danger" type="button" wire:click="deleteSite">Delete Site</flux:button>
            </div>
        </div>
    </flux:modal>
    </div>
</div>
