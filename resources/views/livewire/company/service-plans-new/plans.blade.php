<!-- Plans & Revisions Tab -->
<div class="space-y-6">
    @if($servicePlans->count() > 0)
        @foreach($servicePlans as $plan)
            <div class="border border-gray-200 dark:border-gray-700 rounded-lg">
                <!-- Plan Header -->
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-3 h-3 rounded-full" style="background-color: {{ $plan->color }}"></div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $plan->name }}</h3>
                                @if($plan->description)
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $plan->description }}</p>
                                @endif
                            </div>
                            @if($plan->is_active)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-300">
                                    Active
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300">
                                    Inactive
                                </span>
                            @endif
                        </div>
                        <div class="flex items-center gap-2">
                            <flux:button size="sm" variant="ghost" wire:click="openCreateRevisionModal({{ $plan->id }})">
                                <flux:icon name="plus" class="size-4" />
                                New Revision
                            </flux:button>
                            <flux:dropdown>
                                <flux:button size="sm" variant="ghost" icon="ellipsis-horizontal" />

                                <flux:menu>
                                    <flux:menu.item icon="pencil" wire:click="editPlan({{ $plan->id }})">Edit Plan</flux:menu.item>
                                    <flux:menu.item icon="document-duplicate" wire:click="duplicatePlan({{ $plan->id }})">Duplicate Plan</flux:menu.item>
                                    <flux:menu.separator />
                                    <flux:menu.item icon="archive-box" variant="danger" wire:click="archivePlan({{ $plan->id }})">Archive Plan</flux:menu.item>
                                </flux:menu>
                            </flux:dropdown>
                        </div>
                    </div>
                </div>

                <!-- Revisions -->
                <div class="p-6">
                    @if($plan->revisions->count() > 0)
                        <div class="space-y-4">
                            @foreach($plan->revisions as $revision)
                                <div class="border border-gray-100 dark:border-gray-800 rounded-lg {{ $revision->is_current ? 'ring-2 ring-blue-500' : '' }}">
                                    <!-- Revision Header -->
                                    <div class="p-4 border-b border-gray-100 dark:border-gray-800">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-3">
                                                <div>
                                                    <div class="flex items-center gap-2">
                                                        <h4 class="font-medium text-gray-900 dark:text-white">{{ $revision->name }}</h4>
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                                            {{ $revision->status === 'published' ? 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-300' : '' }}
                                                            {{ $revision->status === 'draft' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-300' : '' }}
                                                            {{ $revision->status === 'archived' ? 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300' : '' }}">
                                                            {{ ucfirst($revision->status) }}
                                                        </span>
                                                        @if($revision->is_current)
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-300">
                                                                Current
                                                            </span>
                                                        @endif
                                                    </div>
                                                    @if($revision->description)
                                                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $revision->description }}</p>
                                                    @endif
                                                    <div class="text-xs text-gray-400 mt-1">
                                                        Version {{ $revision->version_number }} •
                                                        {{ $revision->created_at->format('M j, Y') }}
                                                        @if($revision->published_at)
                                                            • Published {{ $revision->published_at->format('M j, Y') }}
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                @if($revision->isDraft())
                                                    <flux:button size="xs" variant="primary" wire:click="publishRevision({{ $revision->id }})">
                                                        Publish
                                                    </flux:button>
                                                @endif
                                                <flux:button size="xs" variant="ghost" wire:click="openCreateLevelModal({{ $revision->id }})">
                                                    <flux:icon name="plus" class="size-4" />
                                                    Add Level
                                                </flux:button>
                                                <flux:dropdown>
                                                    <flux:button size="xs" variant="ghost" icon="ellipsis-horizontal" />

                                                    <flux:menu>
                                                        <flux:menu.item icon="pencil" wire:click="editRevision({{ $revision->id }})">Edit Revision</flux:menu.item>
                                                        @if(!$revision->is_current)
                                                            <flux:menu.separator />
                                                            <flux:menu.item icon="archive-box" variant="danger" wire:click="archiveRevision({{ $revision->id }})">Archive Revision</flux:menu.item>
                                                        @endif
                                                    </flux:menu>
                                                </flux:dropdown>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Levels -->
                                    @if($revision->levels->count() > 0)
                                        <div class="p-4">
                                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                                @foreach($revision->levels as $level)
                                                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 {{ $level->is_featured ? 'ring-2 ring-blue-500' : '' }}">
                                                        <div class="flex items-center justify-between mb-2">
                                                            <div class="flex items-center gap-2">
                                                                <div class="w-2 h-2 rounded-full" style="background-color: {{ $level->color }}"></div>
                                                                <h5 class="font-medium text-gray-900 dark:text-white">{{ $level->name }}</h5>
                                                                @if($level->is_featured)
                                                                    <flux:icon name="star" class="size-4 text-yellow-500" />
                                                                @endif
                                                            </div>
                                                            <flux:dropdown>
                                                                <flux:button size="xs" variant="ghost" icon="ellipsis-horizontal" />

                                                                <flux:menu>
                                                                    <flux:menu.item icon="pencil" wire:click="editLevel({{ $level->id }})">Edit Level</flux:menu.item>
                                                                    <flux:menu.item icon="cog-6-tooth" wire:click="manageLevelFeatures({{ $level->id }})">Manage Features</flux:menu.item>
                                                                </flux:menu>
                                                            </flux:dropdown>
                                                        </div>
                                                        @if($level->description)
                                                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">{{ $level->description }}</p>
                                                        @endif
                                                        @if($level->monthly_price)
                                                            <div class="text-lg font-semibold text-gray-900 dark:text-white">
                                                                {{ $level->getFormattedPrice('monthly') }}/month
                                                            </div>
                                                        @endif
                                                        <div class="flex items-center justify-between mt-2 text-xs text-gray-500">
                                                            <span>{{ $level->featureValues->count() }} features</span>
                                                            <span class="{{ $level->is_active ? 'text-green-600' : 'text-gray-400' }}">
                                                                {{ $level->is_active ? 'Active' : 'Inactive' }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @else
                                        <div class="p-4 text-center text-gray-500 dark:text-gray-400">
                                            <flux:icon name="clipboard-document-list" class="mx-auto h-8 w-8 mb-2" />
                                            <p class="text-sm">No levels in this revision</p>
                                            <flux:button size="xs" variant="ghost" wire:click="openCreateLevelModal({{ $revision->id }})" class="mt-2">
                                                Add Level
                                            </flux:button>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                            <flux:icon name="document-text" class="mx-auto h-12 w-12 mb-2" />
                            <p>No revisions yet</p>
                            <flux:button size="sm" variant="ghost" wire:click="openCreateRevisionModal({{ $plan->id }})" class="mt-2">
                                Create First Revision
                            </flux:button>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    @else
        <!-- No Plans -->
        <div class="text-center py-12">
            <flux:icon name="clipboard-document-list" class="mx-auto h-12 w-12 text-gray-400" />
            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No service plans</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Get started by creating your first service plan.
            </p>
            <div class="mt-6">
                <flux:button size="sm" wire:click="openCreatePlanModal">
                    <flux:icon name="plus" class="size-4" />
                    Create Service Plan
                </flux:button>
            </div>
        </div>
    @endif
</div>
