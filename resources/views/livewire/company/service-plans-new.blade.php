<div class="flex h-full w-full flex-1 flex-col gap-4">
    @if($currentView === 'list')
        <!-- List View -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-neutral-200 dark:border-neutral-700 p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        Service Plans
                    </h1>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">
                        Create and manage service plans with revisions and levels.
                    </p>
                </div>
                <div class="flex gap-2">
                    <flux:button size="sm" variant="ghost" wire:click="openCreatePlanModal">
                        <flux:icon name="plus" class="size-4" />
                        New Plan
                    </flux:button>
                </div>
            </div>
        </div>

        <!-- Plans List -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-neutral-200 dark:border-neutral-700 flex-1 overflow-hidden">
            <div class="h-full overflow-y-auto p-6">
                @include('livewire.company.service-plans-new.list')
            </div>
        </div>
    @else
        <!-- Edit View -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-neutral-200 dark:border-neutral-700 p-6">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-3">
                    <flux:button size="sm" variant="ghost" wire:click="backToList">
                        <flux:icon name="arrow-left" class="size-4" />
                        Back to Plans
                    </flux:button>
                    @if($editingPlanData)
                        <div class="border-l border-gray-300 pl-3">
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                                {{ $editingPlanData->name }}
                            </h1>
                            <p class="text-gray-600 dark:text-gray-400 mt-1">
                                @if($editingRevisionData)
                                    {{ $editingRevisionData->name }} ({{ ucfirst($editingRevisionData->status) }})
                                @endif
                            </p>
                        </div>
                    @endif
                </div>
                <div class="flex gap-2">
                    @if($editingRevisionData)
                        <flux:button size="sm" variant="ghost" wire:click="openCreateLevelModal({{ $editingRevisionData->id }})">
                            <flux:icon name="plus" class="size-4" />
                            Add Level
                        </flux:button>
                    @endif
                    <flux:button size="sm" variant="ghost" wire:click="openCreateFeatureGroupModal">
                        <flux:icon name="squares-2x2" class="size-4" />
                        New Feature Group
                    </flux:button>
                </div>
            </div>

            <!-- Edit Tabs -->
            @if($editingPlanData)
                <div class="border-b border-gray-200 dark:border-gray-700">
                    <nav class="-mb-px flex space-x-8">
                        <button wire:click="$set('activeEditTab', 'levels')"
                                class="py-2 px-1 border-b-2 font-medium text-sm {{ $activeEditTab === 'levels' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                            Levels
                        </button>
                        <button wire:click="$set('activeEditTab', 'features')"
                                class="py-2 px-1 border-b-2 font-medium text-sm {{ $activeEditTab === 'features' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                            Feature Groups
                        </button>
                        <button wire:click="$set('activeEditTab', 'comparison')"
                                class="py-2 px-1 border-b-2 font-medium text-sm {{ $activeEditTab === 'comparison' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                            Comparison Grid
                        </button>
                    </nav>
                </div>
            @endif
        </div>

        <!-- Edit Content -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-neutral-200 dark:border-neutral-700 flex-1 overflow-hidden">
            <div class="h-full overflow-y-auto p-6">
                @if($activeEditTab === 'levels')
                    @include('livewire.company.service-plans-new.edit-levels')
                @elseif($activeEditTab === 'features')
                    @include('livewire.company.service-plans-new.edit-features')
                @elseif($activeEditTab === 'comparison')
                    @include('livewire.company.service-plans-new.edit-comparison')
                @endif
            </div>
        </div>
    @endif

    <!-- Modals -->
    @include('livewire.company.service-plans-new.modals')
</div>
