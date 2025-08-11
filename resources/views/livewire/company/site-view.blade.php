<div class="flex h-full w-full flex-1 flex-col py-2">
    <div class="p-6 flex flex-col gap-6">

        <!-- Site Header -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-neutral-200 dark:border-neutral-700 p-6">
            <div class="flex items-start justify-between">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0 h-16 w-16">
                        <div class="h-16 w-16 rounded-xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                            <flux:icon name="building-office" class="h-8 w-8 text-blue-600 dark:text-blue-400" />
                        </div>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                            {{ $site->name }}
                        </h1>
                        @if($site->address)
                            <p class="text-gray-600 dark:text-gray-400 mt-2">
                                {{ $site->address }}
                            </p>
                        @endif
                        <div class="flex items-center gap-3 mt-4">
                            <span class="text-sm text-gray-500">Created {{ $site->created_at->format('M j, Y') }}</span>
                            @if($site->siteGroups && count($site->siteGroups) > 0)
                                <div class="flex items-center gap-2">
                                    <span class="text-sm text-gray-500">Groups:</span>
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($site->siteGroups as $group)
                                            <flux:badge color="{{ $group->color ?? 'blue' }}" size="sm">{{ $group->name }}</flux:badge>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <flux:button
                        variant="primary"
                        icon="pencil"
                        :href="route('company.sites', ['company' => $company->id])"
                        wire:navigate
                    >
                        Edit Site
                    </flux:button>
                </div>
            </div>
        </div>

        <!-- Site Details -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            <!-- Basic Information -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-neutral-200 dark:border-neutral-700 p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    Basic Information
                </h2>
                <div class="space-y-4">
                    <div>
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Site Name</label>
                        <p class="text-gray-900 dark:text-white mt-1">{{ $site->name }}</p>
                    </div>
                    @if($site->address)
                        <div>
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Address</label>
                            <p class="text-gray-900 dark:text-white mt-1">{{ $site->address }}</p>
                        </div>
                    @endif
                    <div>
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Created</label>
                        <p class="text-gray-900 dark:text-white mt-1">{{ $site->created_at->format('F j, Y \a\t g:i A') }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Last Updated</label>
                        <p class="text-gray-900 dark:text-white mt-1">{{ $site->updated_at->format('F j, Y \a\t g:i A') }}</p>
                    </div>
                </div>
            </div>

            <!-- Site Groups -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-neutral-200 dark:border-neutral-700 p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    Site Groups
                </h2>
                @if($site->siteGroups && count($site->siteGroups) > 0)
                    <div class="space-y-3">
                        @foreach($site->siteGroups as $group)
                            <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <flux:badge variant="solid" color="{{ $group->color ?? 'blue' }}" class="w-3 h-3 p-0"></flux:badge>
                                <div class="flex-1">
                                    <div class="font-medium text-gray-900 dark:text-white">{{ $group->name }}</div>
                                    @if($group->description)
                                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ $group->description }}</div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <flux:icon name="folder" class="h-12 w-12 text-gray-400 mx-auto mb-3" />
                        <p class="text-gray-500 dark:text-gray-400">This site is not assigned to any groups</p>
                        <flux:button
                            variant="outline"
                            size="sm"
                            class="mt-3"
                            :href="route('company.sites', ['company' => $company->id])"
                            wire:navigate
                        >
                            Manage Groups
                        </flux:button>
                    </div>
                @endif
            </div>

        </div>

        <!-- Quick Actions -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-neutral-200 dark:border-neutral-700 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                Quick Actions
            </h2>
            <div class="flex flex-wrap gap-3">
                <flux:button
                    variant="outline"
                    icon="pencil"
                    :href="route('company.sites', ['company' => $company->id])"
                    wire:navigate
                >
                    Edit Site Details
                </flux:button>
                <flux:button
                    variant="outline"
                    icon="folder"
                    :href="route('company.sites.groups', ['company' => $company->id])"
                    wire:navigate
                >
                    Manage Groups
                </flux:button>
                <flux:button
                    variant="outline"
                    icon="arrow-left"
                    :href="route('company.sites', ['company' => $company->id])"
                    wire:navigate
                >
                    Back to Sites
                </flux:button>
            </div>
        </div>

    </div>
</div>