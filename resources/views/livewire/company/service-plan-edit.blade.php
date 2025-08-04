<div class="flex h-full w-full flex-1 flex-col gap-4">
    <!-- Header -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-neutral-200 dark:border-neutral-700 p-6">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
                <flux:button size="sm" variant="ghost" href="{{ route('company.service', $company) }}">
                    <flux:icon name="arrow-left" class="size-4" />
                    Back to Service Plans
                </flux:button>
                <div class="border-l border-gray-300 pl-3">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ $planData->name }}
                    </h1>
                    @if($revisionData)
                        <div class="flex items-center gap-2 mt-1">
                            <p class="text-gray-600 dark:text-gray-400">
                                {{ $revisionData->name }}
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                    {{ $revisionData->status === 'published' ? 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-300' : '' }}
                                    {{ $revisionData->status === 'draft' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-300' : '' }}
                                    {{ $revisionData->status === 'archived' ? 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300' : '' }}">
                                    {{ ucfirst($revisionData->status) }}
                                </span>
                                @if($revisionData->is_current)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-300">
                                        Current
                                    </span>
                                @endif
                            </p>
                            <flux:button size="xs" variant="ghost" wire:click="openEditRevisionModal({{ $revisionData->id }})">
                                <flux:icon name="pencil" class="size-3" />
                                Edit
                            </flux:button>
                        </div>
                    @endif
                </div>
            </div>
            <div class="flex gap-2">
                @if($revisionData)
                    <flux:button size="sm" variant="ghost" wire:click="openCreateLevelModal({{ $revisionData->id }})">
                        <flux:icon name="plus" class="size-4" />
                        Add Level
                    </flux:button>
                @endif
                <flux:button size="sm" variant="ghost" wire:click="openCreateRevisionModal">
                    <flux:icon name="document-duplicate" class="size-4" />
                    New Revision
                </flux:button>
                <flux:button size="sm" variant="ghost" wire:click="openCreateFeatureGroupModal">
                    <flux:icon name="squares-2x2" class="size-4" />
                    New Feature Group
                </flux:button>
            </div>
        </div>

        <!-- Revision Selector -->
        @if($planData && $planData->revisions->count() > 1)
            <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4 mb-6">
                <div class="flex items-center gap-4">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Revision:</span>
                    <div class="flex flex-wrap gap-2">
                        @foreach($planData->revisions as $rev)
                            <div class="inline-flex items-center gap-1 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                                {{ $revisionData && $rev->id === $revisionData->id
                                    ? 'bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-300'
                                    : 'bg-white text-gray-700 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700' }}">
                                <a href="{{ route('company.service.plans.edit.revision', ['company' => $company, 'plan' => $planData, 'revision' => $rev]) }}"
                                   class="flex items-center gap-2">
                                    {{ $rev->name }}
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium
                                        {{ $rev->status === 'published' ? 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-300' : '' }}
                                        {{ $rev->status === 'draft' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-300' : '' }}
                                        {{ $rev->status === 'archived' ? 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300' : '' }}">
                                        {{ ucfirst($rev->status) }}
                                    </span>
                                    @if($rev->is_current)
                                        <flux:icon name="star" class="size-3 text-blue-500" />
                                    @endif
                                </a>
                                <flux:dropdown>
                                    <flux:button size="xs" variant="ghost" icon="ellipsis-horizontal" />

                                    <flux:menu>
                                        <flux:menu.item icon="pencil" wire:click="openEditRevisionModal({{ $rev->id }})">Edit Revision</flux:menu.item>
                                        @if($rev->status === 'draft')
                                            <flux:menu.item icon="check" wire:click="publishRevision({{ $rev->id }})">Publish</flux:menu.item>
                                        @endif
                                        @if($rev->status !== 'archived')
                                            <flux:menu.separator />
                                            <flux:menu.item icon="archive-box" variant="danger" wire:click="archiveRevision({{ $rev->id }})">Archive</flux:menu.item>
                                        @endif
                                    </flux:menu>
                                </flux:dropdown>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <!-- Tabs -->
        <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="-mb-px flex space-x-8">
                <button wire:click="$set('activeTab', 'levels')"
                        class="py-2 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'levels' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Levels
                </button>
                <button wire:click="$set('activeTab', 'features')"
                        class="py-2 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'features' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Feature Groups
                </button>
                <button wire:click="$set('activeTab', 'comparison')"
                        class="py-2 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'comparison' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Comparison Grid
                </button>
            </nav>
        </div>
    </div>

    <!-- Content -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-neutral-200 dark:border-neutral-700 flex-1 overflow-hidden">
        <div class="h-full overflow-y-auto p-6">
            @if($activeTab === 'levels')
                @include('livewire.company.service-plan-edit.levels', ['editingRevisionData' => $revisionData])
            @elseif($activeTab === 'features')
                @include('livewire.company.service-plan-edit.features')
            @elseif($activeTab === 'comparison')
                @include('livewire.company.service-plan-edit.comparison', ['editingRevisionData' => $revisionData, 'editingPlanData' => $planData])
            @endif
        </div>
    </div>

    <!-- Modals -->
    @include('livewire.company.service-plan-edit.modals', ['editingRevisionData' => $revisionData])
</div>
