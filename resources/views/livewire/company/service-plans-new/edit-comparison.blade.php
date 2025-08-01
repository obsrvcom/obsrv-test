<!-- Comparison Grid for Editing Plan -->
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

        <!-- Comparison Grid -->
        <div class="overflow-x-auto">
            <div class="min-w-full">
                <!-- Header with Level Names and Prices -->
                <div class="grid grid-cols-{{ min($editingRevisionData->levels->count() + 1, 6) }} gap-4 mb-6">
                    <!-- Empty cell for feature names -->
                    <div class="p-4"></div>

                    @foreach($editingRevisionData->levels as $level)
                        <div class="text-center p-4 border rounded-lg {{ $level->is_featured ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-200 dark:border-gray-700' }}">
                            <h3 class="font-bold text-lg mb-2" style="color: {{ $level->color }}">
                                {{ $level->name }}
                            </h3>
                            @if($level->monthly_price)
                                <div class="text-2xl font-bold text-gray-900 dark:text-white">
                                    {{ $level->getFormattedPrice('monthly') }}
                                    <span class="text-sm text-gray-500">/month</span>
                                </div>
                            @endif
                            @if($level->is_featured)
                                <div class="mt-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-300">
                                        Featured
                                    </span>
                                </div>
                            @endif
                            <div class="mt-2">
                                <flux:button size="xs" variant="ghost" wire:click="manageLevelFeatures({{ $level->id }})">
                                    <flux:icon name="cog-6-tooth" class="size-3" />
                                    Edit Features
                                </flux:button>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Feature Groups and Features -->
                @foreach($featureGroups as $featureGroup)
                    @if($featureGroup->activeFeatures->count() > 0)
                        <!-- Feature Group Header -->
                        <div class="mb-4">
                            <div class="grid grid-cols-{{ min($editingRevisionData->levels->count() + 1, 6) }} gap-4">
                                <div class="col-span-{{ min($editingRevisionData->levels->count() + 1, 6) }} p-3 bg-gray-100 dark:bg-gray-800 rounded-lg">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <h4 class="font-semibold text-gray-900 dark:text-white" style="color: {{ $featureGroup->color }}">
                                                {{ $featureGroup->name }}
                                            </h4>
                                            @if($featureGroup->description)
                                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                                    {{ $featureGroup->description }}
                                                </p>
                                            @endif
                                        </div>
                                        <flux:button size="xs" variant="ghost" wire:click="editFeatureGroup({{ $featureGroup->id }})">
                                            <flux:icon name="pencil" class="size-3" />
                                            Edit Group
                                        </flux:button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Features in this group -->
                        @foreach($featureGroup->activeFeatures as $feature)
                            <div class="grid grid-cols-{{ min($editingRevisionData->levels->count() + 1, 6) }} gap-4 py-3 border-b border-gray-100 dark:border-gray-800">
                                <!-- Feature name -->
                                <div class="flex items-center p-3">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2">
                                            <div class="font-medium text-gray-900 dark:text-white">
                                                {{ $feature->name }}
                                            </div>
                                            @if($feature->affects_sla)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-800 dark:text-orange-300">
                                                    SLA
                                                </span>
                                            @endif
                                            <flux:button size="xs" variant="ghost" wire:click="editFeature({{ $feature->id }})">
                                                <flux:icon name="pencil" class="size-3" />
                                            </flux:button>
                                        </div>
                                        @if($feature->description)
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ $feature->description }}
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Feature values for each level -->
                                @foreach($editingRevisionData->levels as $level)
                                    <div class="flex items-center justify-center p-3 text-center">
                                        @php
                                            $featureValue = $level->featureValues()
                                                ->where('feature_id', $feature->id)
                                                ->first();
                                        @endphp

                                        <div class="flex items-center gap-2">
                                            @if($featureValue)
                                                <span class="font-medium">
                                                    {{ $featureValue->formatted_value }}
                                                </span>
                                            @elseif($feature->isBoolean())
                                                <span class="text-gray-400">✗</span>
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                            <flux:button size="xs" variant="ghost" wire:click="editFeatureValue({{ $level->id }}, {{ $feature->id }})">
                                                <flux:icon name="pencil" class="size-3" />
                                            </flux:button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    @endif
                @endforeach

                @if($featureGroups->count() === 0)
                    <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                        <flux:icon name="squares-2x2" class="mx-auto h-8 w-8 mb-2" />
                        <p class="text-sm">No feature groups yet</p>
                        <flux:button size="xs" variant="ghost" wire:click="openCreateFeatureGroupModal" class="mt-2">
                            Create Feature Group
                        </flux:button>
                    </div>
                @endif
            </div>
        </div>
    </div>
@elseif($editingRevisionData)
    <!-- No levels in selected revision -->
    <div class="text-center py-12">
        <flux:icon name="clipboard-document-list" class="mx-auto h-12 w-12 text-gray-400" />
        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No levels found</h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Add levels to this revision to see the comparison grid.
        </p>
        <div class="mt-6">
            <flux:button size="sm" wire:click="openCreateLevelModal({{ $editingRevisionData->id }})">
                <flux:icon name="plus" class="size-4" />
                Add Level
            </flux:button>
        </div>
    </div>
@else
    <!-- No revision selected -->
    <div class="text-center py-12">
        <flux:icon name="document-text" class="mx-auto h-12 w-12 text-gray-400" />
        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No revision selected</h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Select a revision to view its comparison grid.
        </p>
    </div>
@endif
