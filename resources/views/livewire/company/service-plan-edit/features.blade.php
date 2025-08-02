<!-- Feature Groups Management -->
@if($featureGroups->count() > 0)
    <div class="space-y-6">
        @foreach($featureGroups as $group)
            <div class="border border-gray-200 dark:border-gray-700 rounded-lg">
                <!-- Group Header -->
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-3 h-3 rounded-full" style="background-color: {{ $group->color }}"></div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $group->name }}</h3>
                                @if($group->description)
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $group->description }}</p>
                                @endif
                                <div class="flex items-center gap-4 mt-2 text-xs text-gray-500">
                                    <span>{{ $group->features->count() }} features</span>
                                    <span>Created {{ $group->created_at->format('M j, Y') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            @if($group->is_active)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-300">
                                    Active
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300">
                                    Inactive
                                </span>
                            @endif
                            <flux:button size="sm" wire:click="openCreateFeatureModal({{ $group->id }})">
                                <flux:icon name="plus" class="size-4" />
                                Add Feature
                            </flux:button>
                            <flux:dropdown>
                                <flux:button size="sm" variant="ghost" icon="ellipsis-horizontal" />

                                <flux:menu>
                                    <flux:menu.item icon="pencil" wire:click="editFeatureGroup({{ $group->id }})">Edit Group</flux:menu.item>
                                    <flux:menu.separator />
                                    <flux:menu.item icon="archive-box" variant="danger" wire:click="archiveFeatureGroup({{ $group->id }})">Archive Group</flux:menu.item>
                                </flux:menu>
                            </flux:dropdown>
                        </div>
                    </div>
                </div>

                <!-- Features List -->
                @if($group->activeFeatures->count() > 0)
                    <div class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($group->activeFeatures as $feature)
                            <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-3">
                                            <h4 class="font-medium text-gray-900 dark:text-white">{{ $feature->name }}</h4>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                                {{ $feature->data_type === 'boolean' ? 'bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-300' : '' }}
                                                {{ $feature->data_type === 'text' ? 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-300' : '' }}
                                                {{ $feature->data_type === 'number' ? 'bg-purple-100 text-purple-800 dark:bg-purple-800 dark:text-purple-300' : '' }}
                                                {{ $feature->data_type === 'currency' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-300' : '' }}
                                                {{ $feature->data_type === 'time' ? 'bg-indigo-100 text-indigo-800 dark:bg-indigo-800 dark:text-indigo-300' : '' }}
                                                {{ $feature->data_type === 'select' ? 'bg-pink-100 text-pink-800 dark:bg-pink-800 dark:text-pink-300' : '' }}">
                                                {{ ucfirst($feature->data_type) }}
                                            </span>
                                            @if($feature->affects_sla)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-800 dark:text-orange-300">
                                                    SLA
                                                </span>
                                            @endif
                                            @if($feature->unit)
                                                <span class="text-xs text-gray-500">({{ $feature->unit }})</span>
                                            @endif
                                        </div>
                                        @if($feature->description)
                                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $feature->description }}</p>
                                        @endif
                                        @if($feature->data_type === 'select' && $feature->options)
                                            <div class="flex flex-wrap gap-1 mt-2">
                                                @foreach($feature->getSelectOptions() as $option)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300">
                                                        {{ $option }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-2">
                                        @if($feature->is_active)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-300">
                                                Active
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300">
                                                Inactive
                                            </span>
                                        @endif
                                        <flux:dropdown>
                                            <flux:button size="xs" variant="ghost" icon="ellipsis-horizontal" />

                                            <flux:menu>
                                                <flux:menu.item icon="pencil" wire:click="editFeature({{ $feature->id }})">Edit Feature</flux:menu.item>
                                                @if($feature->data_type === 'select')
                                                    <flux:menu.item icon="cog-6-tooth" wire:click="manageFeatureOptions({{ $feature->id }})">Manage Options</flux:menu.item>
                                                @endif
                                                <flux:menu.separator />
                                                <flux:menu.item icon="archive-box" variant="danger" wire:click="archiveFeature({{ $feature->id }})">Archive Feature</flux:menu.item>
                                            </flux:menu>
                                        </flux:dropdown>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-6 text-center text-gray-500 dark:text-gray-400">
                        <flux:icon name="squares-2x2" class="mx-auto h-8 w-8 mb-2" />
                        <p class="text-sm">No features in this group</p>
                        <flux:button size="xs" variant="ghost" wire:click="openCreateFeatureModal({{ $group->id }})" class="mt-2">
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
            Create feature groups to organize your service plan features.
        </p>
        <div class="mt-6">
            <flux:button size="sm" wire:click="openCreateFeatureGroupModal">
                <flux:icon name="plus" class="size-4" />
                Create Feature Group
            </flux:button>
        </div>
    </div>
@endif
