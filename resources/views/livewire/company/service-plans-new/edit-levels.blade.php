<!-- Edit Levels -->
@if($editingRevisionData && $editingRevisionData->levels->count() > 0)
    <div class="space-y-6">
        <!-- Revision Selector -->
        @if($editingPlanData && $editingPlanData->revisions->count() > 1)
            <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
                <flux:select label="Switch Revision" wire:model.live="editingRevision">
                    @foreach($editingPlanData->revisions as $revision)
                        <option value="{{ $revision->id }}">
                            {{ $revision->name }} ({{ ucfirst($revision->status) }})
                            @if($revision->is_current) - Current @endif
                        </option>
                    @endforeach
                </flux:select>
            </div>
        @endif

        <!-- Levels Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($editingRevisionData->levels as $level)
                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-6 {{ $level->is_featured ? 'ring-2 ring-blue-500' : '' }}">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 rounded-full" style="background-color: {{ $level->color }}"></div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $level->name }}</h3>
                            @if($level->is_featured)
                                <flux:icon name="star" class="size-4 text-yellow-500" />
                            @endif
                        </div>
                        <flux:dropdown>
                            <flux:button size="xs" variant="ghost" icon="ellipsis-horizontal" />

                            <flux:menu>
                                <flux:menu.item icon="pencil" wire:click="editLevel({{ $level->id }})">Edit Level</flux:menu.item>
                                <flux:menu.item icon="cog-6-tooth" wire:click="manageLevelFeatures({{ $level->id }})">Manage Features</flux:menu.item>
                                <flux:menu.separator />
                                <flux:menu.item icon="trash" variant="danger" wire:click="deleteLevel({{ $level->id }})">Delete Level</flux:menu.item>
                            </flux:menu>
                        </flux:dropdown>
                    </div>

                    @if($level->description)
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">{{ $level->description }}</p>
                    @endif

                    <!-- Pricing -->
                    <div class="space-y-3 mb-4">
                        @if($level->monthly_price)
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Monthly:</span>
                                <span class="font-medium text-gray-900 dark:text-white">{{ $level->getFormattedPrice('monthly') }}</span>
                            </div>
                        @endif
                        @if($level->quarterly_price)
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Quarterly:</span>
                                <span class="font-medium text-gray-900 dark:text-white">{{ $level->getFormattedPrice('quarterly') }}</span>
                            </div>
                        @endif
                        @if($level->annual_price)
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Annual:</span>
                                <span class="font-medium text-gray-900 dark:text-white">{{ $level->getFormattedPrice('annual') }}</span>
                            </div>
                        @endif
                        @if($level->minimum_contract_months)
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Min Contract:</span>
                                <span class="font-medium text-gray-900 dark:text-white">{{ $level->minimum_contract_months }} months</span>
                            </div>
                        @endif
                    </div>

                    <!-- Status -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            @if($level->is_active)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-300">
                                    Active
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300">
                                    Inactive
                                </span>
                            @endif
                        </div>
                        <span class="text-xs text-gray-500">{{ $level->featureValues->count() }} features</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@elseif($editingRevisionData)
    <!-- No Levels -->
    <div class="text-center py-12">
        <flux:icon name="clipboard-document-list" class="mx-auto h-12 w-12 text-gray-400" />
        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No levels in this revision</h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Add levels to define different service tiers (e.g., Level 1, Level 2, Premium).
        </p>
        <div class="mt-6">
            <flux:button size="sm" wire:click="openCreateLevelModal({{ $editingRevisionData->id }})">
                <flux:icon name="plus" class="size-4" />
                Add Level
            </flux:button>
        </div>
    </div>
@else
    <!-- No Revision Selected -->
    <div class="text-center py-12">
        <flux:icon name="document-text" class="mx-auto h-12 w-12 text-gray-400" />
        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No revision selected</h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Select a revision to manage its levels.
        </p>
    </div>
@endif
