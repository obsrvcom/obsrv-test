<!-- Service Plans Table -->
@if($servicePlans->count() > 0)
    <div class="space-y-4">
        @foreach($servicePlans as $plan)
            <!-- Plan Row -->
            <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                <!-- Plan Header -->
                <div class="bg-gray-50 dark:bg-gray-900 px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div>
                                <h3 class="font-semibold text-gray-900 dark:text-white text-sm">{{ $plan->name }}</h3>
                                @if($plan->description)
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ Str::limit($plan->description, 80) }}</p>
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="flex items-center gap-2 text-xs text-gray-500">
                                <span>{{ $plan->revisions->count() }} revisions</span>
                                <span>•</span>
                                <span>{{ $plan->created_at->format('M j, Y') }}</span>
                            </div>
                            <flux:dropdown>
                                <flux:button size="xs" variant="ghost" icon="ellipsis-horizontal" />
                                <flux:menu>
                                    <flux:menu.item icon="pencil" wire:click="openEditPlanModal({{ $plan->id }})">Edit Plan</flux:menu.item>
                                    <flux:menu.item icon="plus" wire:click="openCreateRevisionModal({{ $plan->id }})">Add Revision</flux:menu.item>
                                    <flux:menu.separator />
                                    <flux:menu.item icon="document-duplicate" wire:click="duplicatePlan({{ $plan->id }})">Duplicate Plan</flux:menu.item>
                                    <flux:menu.separator />
                                    @php
                                        $hasPublishedRevisions = $plan->revisions->where('status', 'published')->count() > 0;
                                    @endphp
                                    @if($hasPublishedRevisions)
                                        <flux:menu.item icon="archive-box" variant="danger" wire:click="archivePlan({{ $plan->id }})">Archive Plan</flux:menu.item>
                                    @else
                                        <flux:menu.item icon="trash" variant="danger" wire:click="confirmDeletePlan({{ $plan->id }})">Delete Plan</flux:menu.item>
                                    @endif
                                </flux:menu>
                            </flux:dropdown>
                        </div>
                    </div>
                </div>

                <!-- Revisions Table -->
                @if($plan->revisions->count() > 0)
                    <div class="bg-white dark:bg-gray-800">
                        <!-- Table Header -->
                        <div class="border-b border-gray-200 dark:border-gray-700 bg-gray-25 dark:bg-gray-850">
                                                            <div class="grid grid-cols-12 gap-4 px-4 py-2 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                <div class="col-span-5">Revision</div>
                                <div class="col-span-2">Status</div>
                                <div class="col-span-1">Version</div>
                                <div class="col-span-3">Levels</div>
                                <div class="col-span-1">Actions</div>
                            </div>
                        </div>

                        <!-- Table Rows -->
                        <div class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($plan->revisions as $revision)
                                                                                                <div class="grid grid-cols-12 gap-4 px-4 py-3 text-sm hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors {{ $revision->is_current ? 'bg-blue-25 dark:bg-blue-950/20 border-l-4 border-blue-500' : '' }}">
                                    <!-- Revision Name -->
                                    <div class="col-span-5 flex items-center gap-2">
                                        <span class="font-medium text-gray-900 dark:text-white">{{ $revision->name }}</span>
                                        @if($revision->is_current && $revision->status === 'published')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-300">
                                                Current
                                            </span>
                                        @endif
                                    </div>

                                    <!-- Status -->
                                    <div class="col-span-2 flex items-center">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                            {{ $revision->status === 'published' ? 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-300' : '' }}
                                            {{ $revision->status === 'draft' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-300' : '' }}
                                            {{ $revision->status === 'archived' ? 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300' : '' }}">
                                            {{ ucfirst($revision->status) }}
                                        </span>
                                    </div>

                                    <!-- Version -->
                                    <div class="col-span-1 flex items-center">
                                        <span class="text-gray-600 dark:text-gray-400">v{{ $revision->version_number }}</span>
                                    </div>

                                    <!-- Levels Count -->
                                    <div class="col-span-3 flex items-center">
                                        <span class="text-gray-600 dark:text-gray-400">{{ $revision->levels->count() }} levels</span>
                                    </div>

                                    <!-- Actions -->
                                    <div class="col-span-1 flex items-center justify-end">
                                        <flux:dropdown>
                                            <flux:button size="xs" variant="ghost" icon="ellipsis-horizontal" />
                                            <flux:menu>
                                                <flux:menu.item icon="pencil" wire:click="openEditRevisionModal({{ $revision->id }})">Edit Revision</flux:menu.item>
                                                <flux:menu.item icon="cog-6-tooth" wire:click="editPlan({{ $plan->id }}, {{ $revision->id }})">Configure Revision</flux:menu.item>
                                                @if($revision->status === 'draft')
                                                    <flux:menu.separator />
                                                    <flux:menu.item icon="check" wire:click="publishRevision({{ $revision->id }})">Publish Revision</flux:menu.item>
                                                @endif
                                                @php
                                                    $canDelete = !$revision->is_current ||
                                                                ($revision->is_current && $plan->revisions->count() === 1 && $revision->status === 'draft');
                                                @endphp
                                                @if($canDelete)
                                                    <flux:menu.separator />
                                                    <flux:menu.item icon="trash" variant="danger" wire:click="confirmDeleteRevision({{ $revision->id }})">Delete Revision</flux:menu.item>
                                                @endif
                                            </flux:menu>
                                        </flux:dropdown>
                                    </div>
                                </div>
                            @endforeach
                        </div>


                    </div>
                @else
                    <!-- No Revisions -->
                    <div class="px-4 py-6 text-center text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800">
                        <flux:icon name="document-text" class="mx-auto h-6 w-6 mb-2" />
                        <p class="text-sm">No revisions yet</p>
                        <p class="text-xs mt-1">Use the dropdown above to add your first revision</p>
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
