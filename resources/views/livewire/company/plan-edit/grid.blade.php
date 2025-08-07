<!-- Streamlined Grid-based Service Plan Configuration -->
@if($revisionData && $revisionData->levels->count() > 0)
    <div class="space-y-4">
        <!-- Configuration Actions -->
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Configure Plan Features</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">Add levels and assign features in the grid below</p>
            </div>
            <div class="flex gap-2">
                <flux:button size="sm" variant="ghost" wire:click="openCreateLevelModal({{ $revisionData->id }})">
                    <flux:icon name="plus" class="size-4" />
                    Add Level
                </flux:button>
                <flux:button size="sm" variant="ghost" wire:click="openCreateFeatureModal">
                    <flux:icon name="plus" class="size-4" />
                    Add Feature
                </flux:button>
            </div>
        </div>

        <!-- Single Unified Grid -->
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <!-- Header Row with Levels -->
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900 dark:text-white border-r border-gray-200 dark:border-gray-700 w-80">
                                <div class="flex items-center justify-between">
                                    <span>Features</span>
                                    <flux:button size="xs" variant="ghost" wire:click="openCreateFeatureGroupModal">
                                        <flux:icon name="plus" class="size-3" />
                                        Group
                                    </flux:button>
                                </div>
                            </th>
                            @foreach($revisionData->levels as $level)
                                <th class="px-4 py-4 text-center text-sm font-semibold border-r border-gray-200 dark:border-gray-700 min-w-[160px]">
                                    <div class="space-y-2">
                                        <div class="flex items-center justify-center gap-2">
                                            <span class="font-bold text-base" style="color: {{ $level->color }}">
                                                {{ $level->name }}
                                            </span>
                                            <flux:dropdown>
                                                <flux:button size="xs" variant="ghost" icon="ellipsis-horizontal" />
                                                <flux:menu>
                                                    <flux:menu.item icon="pencil" wire:click="editLevel({{ $level->id }})">
                                                        Edit Level
                                                    </flux:menu.item>
                                                    <flux:menu.separator />
                                                    <flux:menu.item icon="trash" variant="danger" wire:click="deleteLevelFromGrid({{ $level->id }})">
                                                        Delete Level
                                                    </flux:menu.item>
                                                </flux:menu>
                                            </flux:dropdown>
                                        </div>
                                        
                                        @if($level->monthly_price)
                                            <div class="text-lg font-bold text-gray-900 dark:text-white">
                                                £{{ number_format($level->monthly_price, 2) }}
                                                <div class="text-xs text-gray-500">/month</div>
                                            </div>
                                        @endif

                                        @if($level->is_featured)
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-300">
                                                Featured
                                            </span>
                                        @endif
                                    </div>
                                </th>
                            @endforeach
                        </tr>
                    </thead>

                    <!-- Feature Groups and Rows -->
                    <tbody>
                        @foreach($featureGroups as $featureGroup)
                            @if($featureGroup->activeFeatures->count() > 0)
                                <!-- Feature Group Header Row -->
                                <tr class="bg-gray-100 dark:bg-gray-800">
                                    <td colspan="{{ $revisionData->levels->count() + 1 }}" class="px-6 py-3 border-b border-gray-200 dark:border-gray-700">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <h5 class="font-semibold text-gray-900 dark:text-white text-sm" style="color: {{ $featureGroup->color }}">
                                                    {{ $featureGroup->name }}
                                                </h5>
                                                @if($featureGroup->description)
                                                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                                                        {{ $featureGroup->description }}
                                                    </p>
                                                @endif
                                            </div>
                                            <flux:button size="xs" variant="ghost" wire:click="editFeatureGroup({{ $featureGroup->id }})">
                                                <flux:icon name="pencil" class="size-3" />
                                                Edit
                                            </flux:button>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Feature Rows -->
                                @foreach($featureGroup->activeFeatures as $feature)
                                    <tr class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                        <!-- Feature name column -->
                                        <td class="px-6 py-4 border-r border-gray-200 dark:border-gray-700">
                                            <div class="flex items-center justify-between">
                                                <div class="flex-1">
                                                    <div class="flex items-center gap-2 mb-1">
                                                        <span class="font-medium text-gray-900 dark:text-white text-sm">
                                                            {{ $feature->name }}
                                                        </span>
                                                        @if($feature->affects_sla)
                                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-800 dark:text-orange-300">
                                                                SLA
                                                            </span>
                                                        @endif
                                                    </div>
                                                    @if($feature->description)
                                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                                            {{ $feature->description }}
                                                        </p>
                                                    @endif
                                                    @if($feature->unit)
                                                        <p class="text-xs text-gray-400 mt-1">Unit: {{ $feature->unit }}</p>
                                                    @endif
                                                </div>
                                                <flux:button size="xs" variant="ghost" wire:click="editFeature({{ $feature->id }})">
                                                    <flux:icon name="pencil" class="size-3" />
                                                </flux:button>
                                            </div>
                                        </td>

                                        <!-- Feature value cells for each level -->
                                        @foreach($revisionData->levels as $level)
                                            <td class="px-4 py-4 text-center border-r border-gray-200 dark:border-gray-700">
                                                @php
                                                    $featureValue = $level->featureValues()
                                                        ->where('feature_id', $feature->id)
                                                        ->first();
                                                    $cellId = $level->id . ':' . $feature->id;
                                                    $isEditing = $editingCell === $cellId;
                                                @endphp

                                                @if($isEditing)
                                                    <!-- Editing mode -->
                                                    <div class="w-full">
                                                        @if($feature->isBoolean())
                                                            <!-- Boolean toggle -->
                                                            <div class="flex items-center justify-center gap-2">
                                                                <flux:checkbox wire:model="cellIncluded" />
                                                                <div class="flex gap-1">
                                                                    <flux:button size="xs" variant="ghost" wire:click="saveCellValue">
                                                                        <flux:icon name="check" class="size-3 text-green-600" />
                                                                    </flux:button>
                                                                    <flux:button size="xs" variant="ghost" wire:click="cancelCellEdit">
                                                                        <flux:icon name="x-mark" class="size-3 text-red-600" />
                                                                    </flux:button>
                                                                </div>
                                                            </div>
                                                        @elseif($feature->isSelect())
                                                            <!-- Select dropdown -->
                                                            <div class="flex items-center gap-1">
                                                                <flux:select wire:model="cellValue" size="sm" class="min-w-0 flex-1">
                                                                    <option value="">Select...</option>
                                                                    @foreach($feature->getSelectOptions() as $option)
                                                                        <option value="{{ $option }}">{{ $option }}</option>
                                                                    @endforeach
                                                                </flux:select>
                                                                <div class="flex gap-1">
                                                                    <flux:button size="xs" variant="ghost" wire:click="saveCellValue">
                                                                        <flux:icon name="check" class="size-3 text-green-600" />
                                                                    </flux:button>
                                                                    <flux:button size="xs" variant="ghost" wire:click="cancelCellEdit">
                                                                        <flux:icon name="x-mark" class="size-3 text-red-600" />
                                                                    </flux:button>
                                                                </div>
                                                            </div>
                                                        @else
                                                            <!-- Text/Number input -->
                                                            <div class="flex items-center gap-1">
                                                                <flux:input
                                                                    wire:model="cellValue"
                                                                    size="sm"
                                                                    type="{{ $feature->isNumber() || $feature->isCurrency() ? 'number' : 'text' }}"
                                                                    step="{{ $feature->isCurrency() ? '0.01' : '1' }}"
                                                                    class="min-w-0 flex-1"
                                                                    wire:keydown.enter="saveCellValue"
                                                                    wire:keydown.escape="cancelCellEdit"
                                                                />
                                                                <div class="flex gap-1">
                                                                    <flux:button size="xs" variant="ghost" wire:click="saveCellValue">
                                                                        <flux:icon name="check" class="size-3 text-green-600" />
                                                                    </flux:button>
                                                                    <flux:button size="xs" variant="ghost" wire:click="cancelCellEdit">
                                                                        <flux:icon name="x-mark" class="size-3 text-red-600" />
                                                                    </flux:button>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                @else
                                                    <!-- Display mode -->
                                                    <div class="cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 rounded p-2 min-h-[2rem] flex items-center justify-center"
                                                         wire:click="startEditingCell({{ $level->id }}, {{ $feature->id }})">
                                                        @if($featureValue)
                                                            <span class="font-medium text-sm">
                                                                {{ $featureValue->formatted_value }}
                                                            </span>
                                                        @elseif($feature->isBoolean())
                                                            <span class="text-gray-400 text-sm">✗</span>
                                                        @else
                                                            <span class="text-gray-400 text-sm">-</span>
                                                        @endif
                                                    </div>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @if($featureGroups->count() === 0)
            <div class="text-center py-12">
                <flux:icon name="squares-2x2" class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No features yet</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Add features to start configuring your service plan levels.
                </p>
                <div class="mt-6 flex gap-2 justify-center">
                    <flux:button size="sm" wire:click="openCreateFeatureGroupModal">
                        <flux:icon name="plus" class="size-4" />
                        Add Feature Group
                    </flux:button>
                    <flux:button size="sm" variant="ghost" wire:click="openCreateFeatureModal">
                        <flux:icon name="plus" class="size-4" />
                        Add Feature
                    </flux:button>
                </div>
            </div>
        @endif

        <!-- Quick Tips -->
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
            <div class="flex items-start gap-3">
                <flux:icon name="light-bulb" class="size-5 text-blue-600 dark:text-blue-400 mt-0.5" />
                <div>
                    <h4 class="text-sm font-medium text-blue-900 dark:text-blue-300">Grid Configuration Tips</h4>
                    <ul class="mt-2 text-sm text-blue-800 dark:text-blue-400 space-y-1">
                        <li>• Click any cell to edit feature values for that level</li>
                        <li>• Features can be reused across multiple service plans</li>
                        <li>• Edit SLA and feature details directly from the grid</li>
                        <li>• Press Enter to save or Escape to cancel when editing</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@elseif($revisionData)
    <!-- No levels in selected revision -->
    <div class="text-center py-12">
        <flux:icon name="clipboard-document-list" class="mx-auto h-12 w-12 text-gray-400" />
        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No levels found</h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Add levels to this revision to start configuring features.
        </p>
        <div class="mt-6">
            <flux:button size="sm" wire:click="openCreateLevelModal({{ $revisionData->id }})">
                <flux:icon name="plus" class="size-4" />
                Add Level
            </flux:button>
        </div>
    </div>
@else
    <!-- No revision available -->
    <div class="text-center py-12">
        <flux:icon name="document-text" class="mx-auto h-12 w-12 text-gray-400" />
        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No revision available</h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Create a revision first to configure its features.
        </p>
        <div class="mt-6">
            <flux:button size="sm" wire:click="openCreateRevisionModal">
                <flux:icon name="plus" class="size-4" />
                Create Revision
            </flux:button>
        </div>
    </div>
@endif
