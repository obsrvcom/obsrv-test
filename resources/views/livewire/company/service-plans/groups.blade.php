@if($servicePlanGroups->count() > 0)
    <div class="space-y-6">
        @foreach($servicePlanGroups as $group)
            <div class="bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $group->name }}</h3>
                            @if($group->is_active)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                                    Active
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300">
                                    Inactive
                                </span>
                            @endif
                        </div>
                        @if($group->description)
                            <p class="text-gray-600 dark:text-gray-400 mb-4">{{ $group->description }}</p>
                        @endif

                        <!-- Plans in this group -->
                        @if($group->servicePlans->count() > 0)
                            <div class="flex flex-wrap gap-2">
                                @foreach($group->servicePlans as $plan)
                                    <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300">
                                        {{ $plan->name }}
                                        @if($plan->base_price_monthly)
                                            - {{ $plan->getFormattedPrice('monthly') }}/mo
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-gray-500 dark:text-gray-400">No plans in this group yet.</p>
                        @endif
                    </div>

                    <div class="flex items-center gap-2 ml-4">
                        <flux:button size="sm" variant="ghost" wire:click="openCreatePlanModal({{ $group->id }})">
                            <flux:icon name="plus" class="size-4" />
                            Add Plan
                        </flux:button>
                        <flux:dropdown>
                            <flux:button size="sm" variant="ghost" icon="ellipsis-horizontal" />

                            <flux:menu>
                                <flux:menu.item icon="pencil" wire:click="editGroup({{ $group->id }})">Edit Group</flux:menu.item>
                                <flux:menu.item icon="document-duplicate" wire:click="duplicateGroup({{ $group->id }})">Duplicate Group</flux:menu.item>
                                <flux:menu.separator />
                                @if($group->is_active)
                                    <flux:menu.item icon="eye-slash" wire:click="deactivateGroup({{ $group->id }})">Deactivate</flux:menu.item>
                                @else
                                    <flux:menu.item icon="eye" wire:click="activateGroup({{ $group->id }})">Activate</flux:menu.item>
                                @endif
                                <flux:menu.separator />
                                <flux:menu.item icon="trash" variant="danger" wire:click="confirmDeleteGroup({{ $group->id }})">Delete Group</flux:menu.item>
                            </flux:menu>
                        </flux:dropdown>
                    </div>
                </div>

                <!-- Plans Grid for this Group -->
                @if($group->servicePlans->count() > 0)
                    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                        @foreach($group->servicePlans as $plan)
                            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                                <div class="flex items-start justify-between mb-3">
                                    <div>
                                        <h4 class="font-medium text-gray-900 dark:text-white">{{ $plan->name }}</h4>
                                        @if($plan->description)
                                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ Str::limit($plan->description, 50) }}</p>
                                        @endif
                                    </div>
                                    @if($plan->is_featured)
                                        <flux:icon name="star" class="size-4 text-yellow-500" />
                                    @endif
                                </div>

                                @if($plan->base_price_monthly)
                                    <div class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                                        {{ $plan->getFormattedPrice('monthly') }}
                                        <span class="text-sm font-normal text-gray-500">/month</span>
                                    </div>
                                @endif

                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        @if($plan->is_active)
                                            <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                                            <span class="text-xs text-gray-500">Active</span>
                                        @else
                                            <div class="w-2 h-2 bg-gray-400 rounded-full"></div>
                                            <span class="text-xs text-gray-500">Inactive</span>
                                        @endif
                                    </div>
                                    <flux:dropdown>
                                        <flux:button size="xs" variant="ghost" icon="ellipsis-horizontal" />

                                        <flux:menu>
                                            <flux:menu.item icon="pencil" wire:click="editPlan({{ $plan->id }})">Edit Plan</flux:menu.item>
                                            <flux:menu.item icon="cog-6-tooth" wire:click="managePlanFeatures({{ $plan->id }})">Manage Features</flux:menu.item>
                                            <flux:menu.separator />
                                            <flux:menu.item icon="trash" variant="danger" wire:click="confirmDeletePlan({{ $plan->id }})">Delete Plan</flux:menu.item>
                                        </flux:menu>
                                    </flux:dropdown>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endforeach
    </div>
@else
    <!-- No Service Plan Groups -->
    <div class="text-center py-16">
        <div class="mx-auto h-16 w-16 rounded-full bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center">
            <flux:icon name="squares-plus" class="h-8 w-8 text-neutral-400" />
        </div>
        <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">No plan groups yet</h3>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 max-w-sm mx-auto">
            Create your first service plan group to organize your service offerings.
        </p>
        <div class="mt-6">
            <flux:button size="sm" wire:click="openCreateGroupModal">
                <flux:icon name="plus" class="size-4" />
                Create First Group
            </flux:button>
        </div>
    </div>
@endif
