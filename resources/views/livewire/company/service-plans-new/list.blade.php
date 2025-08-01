<!-- Service Plans List -->
@if($servicePlans->count() > 0)
    <div class="space-y-6">
        @foreach($servicePlans as $plan)
            <div class="border border-gray-200 dark:border-gray-700 rounded-lg hover:shadow-md transition-shadow">
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
                                <div class="flex items-center gap-4 mt-2 text-xs text-gray-500">
                                    <span>{{ $plan->revisions->count() }} revisions</span>
                                    <span>Created {{ $plan->created_at->format('M j, Y') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            @if($plan->is_active)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-300">
                                    Active
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300">
                                    Inactive
                                </span>
                            @endif
                            <flux:button size="sm" wire:click="editPlan({{ $plan->id }})">
                                <flux:icon name="pencil" class="size-4" />
                                Edit Plan
                            </flux:button>
                            <flux:dropdown>
                                <flux:button size="sm" variant="ghost" icon="ellipsis-horizontal" />

                                <flux:menu>
                                    <flux:menu.item icon="document-duplicate" wire:click="duplicatePlan({{ $plan->id }})">Duplicate Plan</flux:menu.item>
                                    <flux:menu.separator />
                                    <flux:menu.item icon="archive-box" variant="danger" wire:click="archivePlan({{ $plan->id }})">Archive Plan</flux:menu.item>
                                </flux:menu>
                            </flux:dropdown>
                        </div>
                    </div>
                </div>

                <!-- Revisions List -->
                @if($plan->revisions->count() > 0)
                    <div class="p-6">
                        <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-3">Revisions</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($plan->revisions->take(6) as $revision)
                                <div class="border border-gray-100 dark:border-gray-800 rounded-lg p-4 {{ $revision->is_current ? 'ring-2 ring-blue-500' : '' }} hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors cursor-pointer"
                                     wire:click="editPlan({{ $plan->id }}, {{ $revision->id }})">
                                    <div class="flex items-center justify-between mb-2">
                                        <h5 class="font-medium text-gray-900 dark:text-white">{{ $revision->name }}</h5>
                                        <div class="flex items-center gap-1">
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
                                    </div>
                                    @if($revision->description)
                                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">{{ Str::limit($revision->description, 60) }}</p>
                                    @endif
                                    <div class="flex items-center justify-between text-xs text-gray-400">
                                        <span>v{{ $revision->version_number }}</span>
                                        <span>{{ $revision->levels->count() }} levels</span>
                                        <span>{{ $revision->created_at->format('M j') }}</span>
                                    </div>
                                </div>
                            @endforeach

                            @if($plan->revisions->count() > 6)
                                <div class="border border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-4 flex items-center justify-center text-gray-500 dark:text-gray-400">
                                    <span class="text-sm">+{{ $plan->revisions->count() - 6 }} more revisions</span>
                                </div>
                            @endif
                        </div>

                        <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800 flex justify-between items-center">
                            <flux:button size="xs" variant="ghost" wire:click="openCreateRevisionModal({{ $plan->id }})">
                                <flux:icon name="plus" class="size-3" />
                                New Revision
                            </flux:button>
                            <span class="text-xs text-gray-500">
                                Current: {{ $plan->getCurrentRevision()?->name ?? 'None' }}
                            </span>
                        </div>
                    </div>
                @else
                    <div class="p-6 text-center text-gray-500 dark:text-gray-400">
                        <flux:icon name="document-text" class="mx-auto h-8 w-8 mb-2" />
                        <p class="text-sm">No revisions yet</p>
                        <flux:button size="xs" variant="ghost" wire:click="openCreateRevisionModal({{ $plan->id }})" class="mt-2">
                            Create First Revision
                        </flux:button>
                    </div>
                @endif
            </div>
        @endforeach
    </div>
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
