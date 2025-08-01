<!-- Overview Tab: Comparison Grid -->
@if($servicePlans->count() > 0 && $selectedPlanData && $selectedRevisionData)
    <div class="space-y-6">
        <!-- Plan & Revision Selector -->
        <div class="flex items-center gap-4 p-4 bg-gray-50 dark:bg-gray-900 rounded-lg">
            <div class="flex-1">
                <flux:select wire:model.live="selectedPlan" label="Service Plan">
                    @foreach($servicePlans as $plan)
                        <option value="{{ $plan->id }}">{{ $plan->name }}</option>
                    @endforeach
                </flux:select>
            </div>
            @if($selectedPlanData && $selectedPlanData->revisions->count() > 0)
                <div class="flex-1">
                    <flux:select wire:model.live="selectedRevision" label="Revision">
                        @foreach($selectedPlanData->revisions as $revision)
                            <option value="{{ $revision->id }}">
                                {{ $revision->name }}
                                @if($revision->is_current) (Current) @endif
                                - {{ ucfirst($revision->status) }}
                            </option>
                        @endforeach
                    </flux:select>
                </div>
            @endif
        </div>

        @if($selectedRevisionData && $selectedRevisionData->levels->count() > 0)
            <!-- Comparison Grid -->
            <div class="overflow-x-auto">
                <div class="min-w-full">
                    <!-- Header with Level Names and Prices -->
                    <div class="grid grid-cols-{{ min($selectedRevisionData->levels->count() + 1, 6) }} gap-4 mb-6">
                        <!-- Empty cell for feature names -->
                        <div class="p-4"></div>

                        @foreach($selectedRevisionData->levels as $level)
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
                            </div>
                        @endforeach
                    </div>

                    <!-- Feature Groups and Features -->
                    @foreach($featureGroups as $featureGroup)
                        @if($featureGroup->activeFeatures->count() > 0)
                            <!-- Feature Group Header -->
                            <div class="mb-4">
                                <div class="grid grid-cols-{{ min($selectedRevisionData->levels->count() + 1, 6) }} gap-4">
                                    <div class="col-span-{{ min($selectedRevisionData->levels->count() + 1, 6) }} p-3 bg-gray-100 dark:bg-gray-800 rounded-lg">
                                        <h4 class="font-semibold text-gray-900 dark:text-white" style="color: {{ $featureGroup->color }}">
                                            {{ $featureGroup->name }}
                                        </h4>
                                        @if($featureGroup->description)
                                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                                {{ $featureGroup->description }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Features in this group -->
                            @foreach($featureGroup->activeFeatures as $feature)
                                <div class="grid grid-cols-{{ min($selectedRevisionData->levels->count() + 1, 6) }} gap-4 py-3 border-b border-gray-100 dark:border-gray-800">
                                    <!-- Feature name -->
                                    <div class="flex items-center p-3">
                                        <div>
                                            <div class="font-medium text-gray-900 dark:text-white">
                                                {{ $feature->name }}
                                                @if($feature->affects_sla)
                                                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-800 dark:text-orange-300">
                                                        SLA
                                                    </span>
                                                @endif
                                            </div>
                                            @if($feature->description)
                                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                                    {{ $feature->description }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Feature values for each level -->
                                    @foreach($selectedRevisionData->levels as $level)
                                        <div class="flex items-center justify-center p-3 text-center">
                                            @php
                                                $featureValue = $level->featureValues()
                                                    ->where('feature_id', $feature->id)
                                                    ->first();
                                            @endphp

                                            @if($featureValue)
                                                <span class="font-medium">
                                                    {{ $featureValue->formatted_value }}
                                                </span>
                                            @elseif($feature->isBoolean())
                                                <span class="text-gray-400">✗</span>
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        @endif
                    @endforeach
                </div>
            </div>
        @else
            <!-- No levels in selected revision -->
            <div class="text-center py-12">
                <flux:icon name="clipboard-document-list" class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No levels found</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    @if($selectedRevisionData)
                        Add levels to this revision to see the comparison grid.
                    @else
                        Select a revision to view its levels.
                    @endif
                </p>
                @if($selectedRevisionData)
                    <div class="mt-6">
                        <flux:button size="sm" wire:click="openCreateLevelModal({{ $selectedRevision }})">
                            <flux:icon name="plus" class="size-4" />
                            Add Level
                        </flux:button>
                    </div>
                @endif
            </div>
        @endif
    </div>
@else
    <!-- No service plans -->
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
