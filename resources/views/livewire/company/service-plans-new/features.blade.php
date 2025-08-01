<!-- Feature Groups Tab -->
<div class="space-y-6">
    @if($featureGroups->count() > 0)
        @foreach($featureGroups as $featureGroup)
            <div class="border border-gray-200 dark:border-gray-700 rounded-lg">
                <!-- Feature Group Header -->
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-3 h-3 rounded-full" style="background-color: {{ $featureGroup->color }}"></div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $featureGroup->name }}</h3>
                                @if($featureGroup->description)
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $featureGroup->description }}</p>
                                @endif
                            </div>
                            @if($featureGroup->is_active)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-300">
                                    Active
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300">
                                    Inactive
                                </span>
                            @endif
                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $featureGroup->features->count() }} features
                            </span>
                        </div>
                        <div class="flex items-center gap-2">
                            <flux:button size="sm" variant="ghost" wire:click="openCreateFeatureModal({{ $featureGroup->id }})">
                                <flux:icon name="plus" class="size-4" />
                                Add Feature
                            </flux:button>
                            <flux:dropdown>
                                <flux:button size="sm" variant="ghost" icon="ellipsis-horizontal" />

                                <flux:menu>
                                    <flux:menu.item icon="pencil" wire:click="editFeatureGroup({{ $featureGroup->id }})">Edit Group</flux:menu.item>
                                    <flux:menu.separator />
                                    @if($featureGroup->is_active)
                                        <flux:menu.item icon="eye-slash" wire:click="deactivateFeatureGroup({{ $featureGroup->id }})">Deactivate</flux:menu.item>
                                    @else
                                        <flux:menu.item icon="eye" wire:click="activateFeatureGroup({{ $featureGroup->id }})">Activate</flux:menu.item>
                                    @endif
                                </flux:menu>
                            </flux:dropdown>
                        </div>
                    </div>
                </div>

                <!-- Features in this group -->
                @if($featureGroup->features->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Feature
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Data Type
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Unit
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        SLA
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th scope="col" class="relative px-6 py-3">
                                        <span class="sr-only">Actions</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($featureGroup->features as $feature)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div>
                                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                    {{ $feature->name }}
                                                </div>
                                                @if($feature->description)
                                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                                        {{ Str::limit($feature->description, 50) }}
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                {{ $feature->data_type === 'boolean' ? 'bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-300' : '' }}
                                                {{ $feature->data_type === 'text' ? 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-300' : '' }}
                                                {{ $feature->data_type === 'number' ? 'bg-purple-100 text-purple-800 dark:bg-purple-800 dark:text-purple-300' : '' }}
                                                {{ $feature->data_type === 'currency' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-300' : '' }}
                                                {{ $feature->data_type === 'time' ? 'bg-indigo-100 text-indigo-800 dark:bg-indigo-800 dark:text-indigo-300' : '' }}
                                                {{ $feature->data_type === 'select' ? 'bg-pink-100 text-pink-800 dark:bg-pink-800 dark:text-pink-300' : '' }}">
                                                {{ ucfirst($feature->data_type) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ $feature->unit ?: '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($feature->affects_sla)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-800 dark:text-orange-300">
                                                    SLA
                                                </span>
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($feature->is_active)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-300">
                                                    Active
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300">
                                                    Inactive
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <flux:dropdown>
                                                <flux:button size="xs" variant="ghost" icon="ellipsis-horizontal" />

                                                <flux:menu>
                                                    <flux:menu.item icon="pencil" wire:click="editFeature({{ $feature->id }})">Edit Feature</flux:menu.item>
                                                    @if($feature->data_type === 'select')
                                                        <flux:menu.item icon="list-bullet" wire:click="manageFeatureOptions({{ $feature->id }})">Manage Options</flux:menu.item>
                                                    @endif
                                                    <flux:menu.separator />
                                                    @if($feature->is_active)
                                                        <flux:menu.item icon="eye-slash" wire:click="deactivateFeature({{ $feature->id }})">Deactivate</flux:menu.item>
                                                    @else
                                                        <flux:menu.item icon="eye" wire:click="activateFeature({{ $feature->id }})">Activate</flux:menu.item>
                                                    @endif
                                                </flux:menu>
                                            </flux:dropdown>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-6 text-center text-gray-500 dark:text-gray-400">
                        <flux:icon name="puzzle-piece" class="mx-auto h-8 w-8 mb-2" />
                        <p class="text-sm">No features in this group</p>
                        <flux:button size="xs" variant="ghost" wire:click="openCreateFeatureModal({{ $featureGroup->id }})" class="mt-2">
                            Add Feature
                        </flux:button>
                    </div>
                @endif
            </div>
        @endforeach
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
</div>
