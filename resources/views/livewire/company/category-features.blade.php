<div class="flex h-full w-full flex-1 flex-col gap-4">
    <!-- Header -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-neutral-200 dark:border-neutral-700 p-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <flux:button size="sm" variant="ghost" href="{{ route('company.plans.category', ['company' => $company, 'category' => $category]) }}">
                    <flux:icon name="arrow-left" class="size-4" />
                    Back to {{ $category->name }} Plans
                </flux:button>
                <div class="border-l border-gray-300 pl-3">
                    <div class="flex items-center gap-3">
                        @if($category->icon)
                            <div class="p-2 rounded-lg" style="background-color: {{ $category->color }}20;">
                                <flux:icon name="{{ $category->icon }}" class="size-6" style="color: {{ $category->color }}" />
                            </div>
                        @else
                            <div class="p-2 rounded-lg" style="background-color: {{ $category->color }}20;">
                                <flux:icon name="folder" class="size-6" style="color: {{ $category->color }}" />
                            </div>
                        @endif
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $category->name }} Features</h1>
                            <p class="text-gray-600 dark:text-gray-400 mt-1">Manage features for {{ $category->name }} plans</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex gap-2">
                <flux:button size="sm" variant="ghost" wire:click="openCreateGroupModal">
                    <flux:icon name="squares-2x2" class="size-4" />
                    Add Feature Group
                </flux:button>
                <flux:button wire:click="openCreateFeatureModal" variant="primary">
                    <flux:icon name="plus" class="size-4" />
                    Add Feature
                </flux:button>
            </div>
        </div>
    </div>

    <!-- Feature Groups -->
    @foreach($featureGroups as $featureGroup)
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-neutral-200 dark:border-neutral-700 overflow-hidden">
            <!-- Feature Group Header -->
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700" style="background-color: {{ $featureGroup->color }}08;">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="p-2 rounded-lg" style="background-color: {{ $featureGroup->color }}20;">
                            <flux:icon name="squares-2x2" class="size-5" style="color: {{ $featureGroup->color }}" />
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 dark:text-white">{{ $featureGroup->name }}</h3>
                            @if($featureGroup->description)
                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $featureGroup->description }}</p>
                            @endif
                        </div>
                        @if(!$featureGroup->is_active)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300">
                                Inactive
                            </span>
                        @endif
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-gray-500">{{ $featureGroup->features_count }} features</span>
                        <flux:dropdown>
                            <flux:button size="sm" variant="ghost" icon="ellipsis-horizontal" />
                            <flux:menu>
                                <flux:menu.item icon="pencil" wire:click="editFeatureGroup({{ $featureGroup->id }})">
                                    Edit Group
                                </flux:menu.item>
                                <flux:menu.item icon="plus" wire:click="openCreateFeatureModal({{ $featureGroup->id }})">
                                    Add Feature
                                </flux:menu.item>
                                <flux:menu.separator />
                                <flux:menu.item icon="trash" variant="danger">
                                    Delete Group
                                </flux:menu.item>
                            </flux:menu>
                        </flux:dropdown>
                    </div>
                </div>
            </div>

            <!-- Features List -->
            @if($featureGroup->features->count() > 0)
                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($featureGroup->features as $feature)
                        <div class="px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-4">
                                    <div class="flex items-center gap-3">
                                        <div class="p-2 rounded-lg bg-gray-100 dark:bg-gray-800">
                                            <flux:icon name="adjustments-horizontal" class="size-4 text-gray-600 dark:text-gray-400" />
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <h4 class="font-medium text-gray-900 dark:text-white">{{ $feature->name }}</h4>
                                                @if($feature->affects_sla)
                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-800 dark:text-orange-300">
                                                        SLA
                                                    </span>
                                                @endif
                                                @if(!$feature->is_active)
                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300">
                                                        Inactive
                                                    </span>
                                                @endif
                                            </div>
                                            @if($feature->description)
                                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $feature->description }}</p>
                                            @endif
                                            <div class="flex items-center gap-4 mt-1">
                                                <span class="text-xs text-gray-500">Type: {{ ucfirst($feature->data_type) }}</span>
                                                @if($feature->unit)
                                                    <span class="text-xs text-gray-500">Unit: {{ $feature->unit }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <flux:dropdown>
                                    <flux:button size="sm" variant="ghost" icon="ellipsis-horizontal" />
                                    <flux:menu>
                                        <flux:menu.item icon="pencil" wire:click="editFeature({{ $feature->id }})">
                                            Edit Feature
                                        </flux:menu.item>
                                        <flux:menu.separator />
                                        <flux:menu.item icon="trash" variant="danger">
                                            Delete Feature
                                        </flux:menu.item>
                                    </flux:menu>
                                </flux:dropdown>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="px-6 py-8 text-center">
                    <flux:icon name="adjustments-horizontal" class="mx-auto h-8 w-8 text-gray-400" />
                    <h4 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No features yet</h4>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Add features to this group to get started.
                    </p>
                    <div class="mt-4">
                        <flux:button size="sm" wire:click="openCreateFeatureModal({{ $featureGroup->id }})">
                            <flux:icon name="plus" class="size-4" />
                            Add Feature
                        </flux:button>
                    </div>
                </div>
            @endif
        </div>
    @endforeach

    @if($featureGroups->count() === 0)
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-neutral-200 dark:border-neutral-700 p-12 text-center">
            <flux:icon name="squares-2x2" class="mx-auto h-12 w-12 text-gray-400" />
            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No feature groups yet</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Create feature groups to organize your {{ strtolower($category->name) }} plan features.
            </p>
            <div class="mt-6">
                <flux:button wire:click="openCreateGroupModal" size="sm">
                    <flux:icon name="plus" class="size-4" />
                    Create Feature Group
                </flux:button>
            </div>
        </div>
    @endif

    <!-- Create Feature Group Modal -->
    <flux:modal variant="flyout" wire:model.self="showCreateGroupModal" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Create Feature Group</flux:heading>
                <flux:text class="mt-2">Organize features into logical groups for {{ $category->name }} plans.</flux:text>
            </div>

            <form wire:submit="createFeatureGroup" class="space-y-6">
                <flux:input label="Group Name" wire:model="createGroupForm.name" placeholder="e.g., Support Features, SLA Terms" required />
                @error('createGroupForm.name')
                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                @enderror

                <flux:textarea label="Description" wire:model="createGroupForm.description" placeholder="Optional description of this feature group..." rows="3" />
                @error('createGroupForm.description')
                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                @enderror

                <flux:input label="Color" wire:model="createGroupForm.color" type="color" />
                @error('createGroupForm.color')
                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                @enderror

                <flux:checkbox wire:model="createGroupForm.is_active" label="Active" />
                <flux:text size="sm" class="text-gray-600">Active groups are visible in plans.</flux:text>

                <div class="flex">
                    <flux:spacer />
                    <flux:button variant="ghost" type="button" wire:click="$set('showCreateGroupModal', false)">Cancel</flux:button>
                    <flux:button variant="primary" type="submit">Create Group</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

    <!-- Create Feature Modal -->
    <flux:modal variant="flyout" wire:model.self="showCreateFeatureModal" class="md:w-[500px]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Create Feature</flux:heading>
                <flux:text class="mt-2">Add a new feature to {{ $category->name }} plans.</flux:text>
            </div>

            <form wire:submit="createFeature" class="space-y-6">
                <flux:select label="Feature Group" wire:model="createFeatureForm.feature_group_id" placeholder="Select a feature group" required>
                    @foreach($featureGroups as $group)
                        <option value="{{ $group->id }}">{{ $group->name }}</option>
                    @endforeach
                </flux:select>
                @error('createFeatureForm.feature_group_id')
                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                @enderror

                <flux:input label="Feature Name" wire:model="createFeatureForm.name" placeholder="e.g., Response Time, Phone Support" required />
                @error('createFeatureForm.name')
                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                @enderror

                <flux:textarea label="Description" wire:model="createFeatureForm.description" placeholder="Optional description of this feature..." rows="2" />
                @error('createFeatureForm.description')
                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                @enderror

                <div class="grid grid-cols-2 gap-4">
                    <flux:select label="Data Type" wire:model="createFeatureForm.data_type" required>
                        <option value="boolean">Yes/No (Boolean)</option>
                        <option value="text">Text</option>
                        <option value="number">Number</option>
                        <option value="currency">Currency</option>
                        <option value="time">Time Duration</option>
                        <option value="select">Multiple Choice</option>
                    </flux:select>
                    @error('createFeatureForm.data_type')
                        <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                    @enderror

                    <flux:input label="Unit" wire:model="createFeatureForm.unit" placeholder="e.g., hours, GB, %" />
                    @error('createFeatureForm.unit')
                        <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="space-y-3">
                    <flux:checkbox wire:model="createFeatureForm.is_active" label="Active" />
                    <flux:text size="sm" class="text-gray-600">Active features are available for use in plans.</flux:text>

                    <flux:checkbox wire:model="createFeatureForm.affects_sla" label="Affects SLA" />
                    <flux:text size="sm" class="text-gray-600">This feature impacts service level agreements.</flux:text>
                </div>

                <div class="flex">
                    <flux:spacer />
                    <flux:button variant="ghost" type="button" wire:click="$set('showCreateFeatureModal', false)">Cancel</flux:button>
                    <flux:button variant="primary" type="submit">Create Feature</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

    <!-- Edit Feature Group Modal -->
    <flux:modal variant="flyout" wire:model.self="showEditGroupModal" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Edit Feature Group</flux:heading>
                <flux:text class="mt-2">Update the feature group details.</flux:text>
            </div>

            <form wire:submit="updateFeatureGroup" class="space-y-6">
                <flux:input label="Group Name" wire:model="editGroupForm.name" placeholder="e.g., Support Features, SLA Terms" required />
                @error('editGroupForm.name')
                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                @enderror

                <flux:textarea label="Description" wire:model="editGroupForm.description" placeholder="Optional description of this feature group..." rows="3" />
                @error('editGroupForm.description')
                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                @enderror

                <flux:input label="Color" wire:model="editGroupForm.color" type="color" />
                @error('editGroupForm.color')
                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                @enderror

                <flux:checkbox wire:model="editGroupForm.is_active" label="Active" />
                <flux:text size="sm" class="text-gray-600">Active groups are visible in plans.</flux:text>

                <div class="flex">
                    <flux:spacer />
                    <flux:button variant="ghost" type="button" wire:click="$set('showEditGroupModal', false)">Cancel</flux:button>
                    <flux:button variant="primary" type="submit">Update Group</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

    <!-- Edit Feature Modal (same structure as create but with editFeatureForm) -->
    <flux:modal variant="flyout" wire:model.self="showEditFeatureModal" class="md:w-[500px]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Edit Feature</flux:heading>
                <flux:text class="mt-2">Update this feature's details, including SLA settings.</flux:text>
            </div>

            <form wire:submit="updateFeature" class="space-y-6">
                <flux:input label="Feature Name" wire:model="editFeatureForm.name" placeholder="e.g., Response Time, Phone Support" required />
                @error('editFeatureForm.name')
                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                @enderror

                <flux:textarea label="Description" wire:model="editFeatureForm.description" placeholder="Optional description of this feature..." rows="2" />
                @error('editFeatureForm.description')
                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                @enderror

                <div class="grid grid-cols-2 gap-4">
                    <flux:select label="Data Type" wire:model="editFeatureForm.data_type" required>
                        <option value="boolean">Yes/No (Boolean)</option>
                        <option value="text">Text</option>
                        <option value="number">Number</option>
                        <option value="currency">Currency</option>
                        <option value="time">Time Duration</option>
                        <option value="select">Multiple Choice</option>
                    </flux:select>
                    @error('editFeatureForm.data_type')
                        <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                    @enderror

                    <flux:input label="Unit" wire:model="editFeatureForm.unit" placeholder="e.g., hours, GB, %" />
                    @error('editFeatureForm.unit')
                        <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="space-y-4">
                    <div class="p-4 bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800 rounded-lg">
                        <div class="flex items-start gap-3">
                            <flux:checkbox wire:model="editFeatureForm.affects_sla" />
                            <div>
                                <label class="text-sm font-medium text-orange-900 dark:text-orange-300">Affects SLA</label>
                                <p class="text-xs text-orange-800 dark:text-orange-400 mt-1">
                                    Check this if this feature impacts service level agreements and customer commitments.
                                </p>
                            </div>
                        </div>
                    </div>

                    <flux:checkbox wire:model="editFeatureForm.is_active" label="Active" />
                    <flux:text size="sm" class="text-gray-600">Active features are available for use in plans.</flux:text>
                </div>

                <div class="flex">
                    <flux:spacer />
                    <flux:button variant="ghost" type="button" wire:click="$set('showEditFeatureModal', false)">Cancel</flux:button>
                    <flux:button variant="primary" type="submit">Update Feature</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>
</div>