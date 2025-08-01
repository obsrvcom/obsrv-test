@if($featureCategories->count() > 0)
    <div class="space-y-6">
        @foreach($featureCategories as $category)
            <div class="bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            @if($category->color)
                                <div class="w-4 h-4 rounded-full" style="background-color: {{ $category->color }}"></div>
                            @endif
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $category->name }}</h3>
                            @if($category->is_active)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                                    Active
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300">
                                    Inactive
                                </span>
                            @endif
                        </div>
                        @if($category->description)
                            <p class="text-gray-600 dark:text-gray-400 mb-4">{{ $category->description }}</p>
                        @endif

                        <!-- Feature count -->
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ $category->features->count() }} feature{{ $category->features->count() !== 1 ? 's' : '' }}
                        </p>
                    </div>

                    <div class="flex items-center gap-2 ml-4">
                        <flux:button size="sm" variant="ghost" wire:click="openCreateFeatureModal({{ $category->id }})">
                            <flux:icon name="plus" class="size-4" />
                            Add Feature
                        </flux:button>
                        <flux:dropdown>
                            <flux:button size="sm" variant="ghost" icon="ellipsis-horizontal" />

                            <flux:menu>
                                <flux:menu.item icon="pencil" wire:click="editCategory({{ $category->id }})">Edit Category</flux:menu.item>
                                <flux:menu.separator />
                                @if($category->is_active)
                                    <flux:menu.item icon="eye-slash" wire:click="deactivateCategory({{ $category->id }})">Deactivate</flux:menu.item>
                                @else
                                    <flux:menu.item icon="eye" wire:click="activateCategory({{ $category->id }})">Activate</flux:menu.item>
                                @endif
                                <flux:menu.separator />
                                <flux:menu.item icon="trash" variant="danger" wire:click="confirmDeleteCategory({{ $category->id }})">Delete Category</flux:menu.item>
                            </flux:menu>
                        </flux:dropdown>
                    </div>
                </div>

                <!-- Features in this category -->
                @if($category->features->count() > 0)
                    <div class="mt-6">
                        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-900">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                Feature
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                Type
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                Unit
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                SLA
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                Status
                                            </th>
                                            <th class="relative px-6 py-3">
                                                <span class="sr-only">Actions</span>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                        @foreach($category->features as $feature)
                                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-900">
                                                <td class="px-6 py-4">
                                                    <div>
                                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                            {{ $feature->name }}
                                                        </div>
                                                        @if($feature->description)
                                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                                {{ Str::limit($feature->description, 60) }}
                                                            </div>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                        {{ $feature->data_type === 'boolean' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' : '' }}
                                                        {{ $feature->data_type === 'currency' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300' : '' }}
                                                        {{ $feature->data_type === 'time' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300' : '' }}
                                                        {{ !in_array($feature->data_type, ['boolean', 'currency', 'time']) ? 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300' : '' }}">
                                                        {{ ucfirst($feature->data_type) }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                    {{ $feature->unit ?: '-' }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    @if($feature->affects_sla)
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300">
                                                            <flux:icon name="clock" class="size-3 mr-1" />
                                                            Affects SLA
                                                        </span>
                                                    @else
                                                        <span class="text-gray-400">-</span>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    @if($feature->is_active)
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
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
                                                            <flux:menu.separator />
                                                            <flux:menu.item icon="trash" variant="danger" wire:click="confirmDeleteFeature({{ $feature->id }})">Delete Feature</flux:menu.item>
                                                        </flux:menu>
                                                    </flux:dropdown>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="mt-6 text-center py-8 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                        <flux:icon name="puzzle-piece" class="mx-auto h-8 w-8 text-gray-400" />
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">No features in this category yet.</p>
                        <div class="mt-4">
                            <flux:button size="sm" variant="ghost" wire:click="openCreateFeatureModal({{ $category->id }})">
                                <flux:icon name="plus" class="size-4" />
                                Add First Feature
                            </flux:button>
                        </div>
                    </div>
                @endif
            </div>
        @endforeach
    </div>
@else
    <!-- No Feature Categories -->
    <div class="text-center py-16">
        <div class="mx-auto h-16 w-16 rounded-full bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center">
            <flux:icon name="squares-2x2" class="h-8 w-8 text-neutral-400" />
        </div>
        <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">No feature categories yet</h3>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 max-w-sm mx-auto">
            Create feature categories to organize the different aspects of your service plans.
        </p>
        <div class="mt-6">
            <flux:button size="sm" wire:click="openCreateCategoryModal">
                <flux:icon name="plus" class="size-4" />
                Create First Category
            </flux:button>
        </div>
    </div>
@endif
