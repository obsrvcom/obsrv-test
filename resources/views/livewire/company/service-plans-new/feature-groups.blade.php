<!-- Feature Groups List -->
@if($featureGroups->count() > 0)
    <div class="space-y-4">
        @foreach($featureGroups as $group)
            <!-- Feature Group Card -->
            <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                <!-- Group Header -->
                <div class="bg-gray-50 dark:bg-gray-900 px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-3 h-3 rounded-full flex-shrink-0" style="background-color: {{ $group->color }}"></div>
                            <div>
                                <h3 class="font-semibold text-gray-900 dark:text-white text-sm">{{ $group->name }}</h3>
                                @if($group->description)
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ Str::limit($group->description, 80) }}</p>
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="flex items-center gap-2 text-xs text-gray-500">
                                <span>{{ $group->features->count() }} features</span>
                                <span>•</span>
                                <span>{{ $group->created_at->format('M j, Y') }}</span>
                            </div>
                            <flux:dropdown>
                                <flux:button size="xs" variant="ghost" icon="ellipsis-horizontal" />
                                <flux:menu>
                                    <flux:menu.item icon="pencil" wire:click="editFeatureGroup({{ $group->id }})">Edit Group</flux:menu.item>
                                    <flux:menu.item icon="plus" wire:click="openCreateFeatureModal({{ $group->id }})">Add Feature</flux:menu.item>
                                </flux:menu>
                            </flux:dropdown>
                        </div>
                    </div>
                </div>

                <!-- Features List -->
                @if($group->features->count() > 0)
                    <div class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($group->features as $feature)
                            <div class="px-4 py-3 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors">
                                <div class="flex items-center gap-3">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $feature->name }}</div>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300">
                                        {{ ucfirst($feature->data_type) }}
                                    </span>
                                    @if($feature->unit)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-300">
                                            {{ $feature->unit }}
                                        </span>
                                    @endif
                                    @if($feature->affects_sla)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-800 dark:text-orange-300">
                                            SLA
                                        </span>
                                    @endif
                                    @if(!$feature->is_active)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-300">
                                            Inactive
                                        </span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-1">
                                    @if($feature->description)
                                        <span class="text-xs text-gray-500 dark:text-gray-400 max-w-xs truncate" title="{{ $feature->description }}">
                                            {{ Str::limit($feature->description, 40) }}
                                        </span>
                                    @endif
                                    <flux:dropdown>
                                        <flux:button size="xs" variant="ghost" icon="ellipsis-horizontal" />
                                        <flux:menu>
                                            <flux:menu.item icon="pencil" wire:click="editFeature({{ $feature->id }})">Edit Feature</flux:menu.item>
                                            @if($feature->data_type === 'select')
                                                <flux:menu.item icon="list-bullet" wire:click="manageFeatureOptions({{ $feature->id }})">Manage Options</flux:menu.item>
                                            @endif
                                            <flux:menu.separator />
                                            @if($feature->is_active)
                                                <flux:menu.item icon="pause" wire:click="deactivateFeature({{ $feature->id }})">Deactivate</flux:menu.item>
                                            @else
                                                <flux:menu.item icon="play" wire:click="activateFeature({{ $feature->id }})">Activate</flux:menu.item>
                                            @endif
                                        </flux:menu>
                                    </flux:dropdown>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <!-- No Features -->
                    <div class="px-4 py-6 text-center text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800">
                        <flux:icon name="cog-6-tooth" class="mx-auto h-6 w-6 mb-2" />
                        <p class="text-sm mb-2">No features in this group yet</p>
                        <flux:button size="xs" variant="ghost" wire:click="openCreateFeatureModal({{ $group->id }})">
                            <flux:icon name="plus" class="size-3" />
                            Add First Feature
                        </flux:button>
                    </div>
                @endif
            </div>
        @endforeach
    </div>
@else
    <!-- No Feature Groups -->
    <div class="text-center py-12">
        <flux:icon name="squares-2x2" class="mx-auto h-12 w-12 text-gray-400" />
        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No feature groups</h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Get started by creating your first feature group to organize your service plan features.
        </p>
        <div class="mt-6">
            <flux:button size="sm" wire:click="openCreateFeatureGroupModal">
                <flux:icon name="plus" class="size-4" />
                Create Feature Group
            </flux:button>
        </div>
    </div>
@endif
