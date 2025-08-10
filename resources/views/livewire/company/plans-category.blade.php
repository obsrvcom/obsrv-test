<div class="flex h-full w-full flex-1 flex-col gap-4">
    <!-- Header -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-neutral-200 dark:border-neutral-700 p-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <flux:button size="sm" variant="ghost" href="{{ route('company.plans', $company) }}">
                    <flux:icon name="arrow-left" class="size-4" />
                    Back to Categories
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
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $category->name }} Plans</h1>
                            @if($category->description)
                                <p class="text-gray-600 dark:text-gray-400 mt-1">{{ $category->description }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <flux:button wire:click="openCreatePlanModal" variant="primary" icon="plus">
                Add Plan
            </flux:button>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="bg-white dark:bg-gray-800 border-b border-neutral-200 dark:border-neutral-700">
        <div class="px-6">
            <flux:navbar>
                <flux:navbar.item
                    href="{{ route('company.plans.category', ['company' => $company, 'category' => $category]) }}"
                    :current="request()->routeIs('company.plans.category')"
                    icon="document-text"
                    wire:navigate
                >
                    Plans ({{ $category->plans_count ?? $category->plans()->count() }})
                </flux:navbar.item>
                <flux:navbar.item
                    href="{{ route('company.features.category', ['company' => $company, 'category' => $category]) }}"
                    :current="request()->routeIs('company.features.category')"
                    icon="cog-6-tooth"
                    wire:navigate
                >
                    Features ({{ $category->feature_groups_count ?? $category->featureGroups()->count() }})
                </flux:navbar.item>
            </flux:navbar>
        </div>
    </div>

    <!-- Plans Table -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-neutral-200 dark:border-neutral-700 overflow-hidden">
        @if($plans->count() > 0)
            <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
                <thead class="bg-neutral-50 dark:bg-neutral-900">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                            Plan
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                            Description
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                            Revisions & Configuration
                        </th>
                        <th class="px-6 py-4 text-right text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-neutral-200 dark:divide-neutral-700">
                    @foreach($plans as $plan)
                        <!-- Plan Row -->
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 rounded-lg" style="background-color: {{ $plan->color }}20;">
                                        <flux:icon name="document-text" class="size-4" style="color: {{ $plan->color }}" />
                                    </div>
                                    <span class="font-semibold text-gray-900 dark:text-white">{{ $plan->name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-gray-600 dark:text-gray-400 text-sm">
                                    {{ $plan->description ?: 'No description' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($plan->is_active)
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100">
                                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full mr-1.5"></span>
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300">
                                        <span class="w-1.5 h-1.5 bg-gray-500 rounded-full mr-1.5"></span>
                                        Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="text-lg font-semibold text-gray-900 dark:text-white">{{ $plan->revisions_count }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <flux:dropdown>
                                        <flux:button size="sm" variant="ghost" icon="ellipsis-horizontal" />
                                        <flux:menu>
                                            <flux:menu.item icon="pencil" wire:click="editPlan({{ $plan->id }})">
                                                Edit Plan
                                            </flux:menu.item>
                                            <flux:menu.item icon="document-duplicate" wire:click="duplicatePlan({{ $plan->id }})">
                                                Duplicate Plan
                                            </flux:menu.item>
                                            <flux:menu.separator />
                                            <flux:menu.item icon="trash" variant="danger" wire:click="confirmDeletePlan({{ $plan->id }})">
                                                Delete Plan
                                            </flux:menu.item>
                                        </flux:menu>
                                    </flux:dropdown>
                                </div>
                            </td>
                        </tr>

                        <!-- Revision Sub-rows -->
                        @foreach($plan->revisions as $revision)
                            <tr class="bg-gray-50 dark:bg-gray-900">
                                <td class="px-6 py-3 whitespace-nowrap">
                                    <div class="flex items-center gap-3 pl-8">
                                        <flux:icon name="arrow-turn-down-right" class="size-3 text-gray-400" />
                                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ $revision->name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-3">
                                    <span class="text-gray-500 dark:text-gray-400 text-xs">
                                        {{ $revision->description ?: 'No description' }}
                                    </span>
                                </td>
                                <td class="px-6 py-3 whitespace-nowrap">
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium
                                        {{ $revision->status === 'published' ? 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-300' : '' }}
                                        {{ $revision->status === 'draft' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-300' : '' }}
                                        {{ $revision->status === 'archived' ? 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300' : '' }}">
                                        {{ ucfirst($revision->status) }}
                                    </span>
                                    @if($revision->is_current)
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-300 ml-1">
                                            Current
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-3 whitespace-nowrap text-center">
                                    <span class="text-sm text-gray-500 dark:text-gray-400">v{{ $revision->version_number }}</span>
                                </td>
                                <td class="px-6 py-3 whitespace-nowrap text-right">
                                    <flux:button size="sm" variant="primary" icon="cog-6-tooth" href="{{ route('company.plans.edit.revision', ['company' => $company, 'plan' => $plan, 'revision' => $revision]) }}">
                                        Configure
                                    </flux:button>
                                </td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="text-center py-12">
                <flux:icon name="document-plus" class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No plans yet</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Create your first {{ strtolower($category->name) }} plan to get started.
                </p>
                <div class="mt-6">
                    <flux:button wire:click="openCreatePlanModal" size="sm" icon="plus">
                        Create Plan
                    </flux:button>
                </div>
            </div>
        @endif
    </div>

    <!-- Edit Plan Modal -->
    <flux:modal variant="flyout" wire:model.self="showEditPlanModal" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Edit Plan</flux:heading>
                <flux:text class="mt-2">Update the plan details.</flux:text>
            </div>

            <form wire:submit="updatePlan" class="space-y-6">
                <flux:input label="Plan Name" wire:model="editPlanForm.name" placeholder="e.g., Standard Service Plan" required />
                @error('editPlanForm.name')
                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                @enderror

                <flux:textarea label="Description" wire:model="editPlanForm.description" placeholder="Optional description of this plan..." rows="3" />
                @error('editPlanForm.description')
                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                @enderror

                <flux:input label="Color" wire:model="editPlanForm.color" type="color" />
                @error('editPlanForm.color')
                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                @enderror

                <flux:checkbox wire:model="editPlanForm.is_active" label="Active" />
                <flux:text size="sm" class="text-gray-600">Active plans are visible and can be used.</flux:text>

                <div class="flex">
                    <flux:spacer />
                    <flux:button variant="ghost" type="button" wire:click="$set('showEditPlanModal', false)">Cancel</flux:button>
                    <flux:button variant="primary" type="submit">Update Plan</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

    <!-- Create Plan Modal -->
    <flux:modal variant="flyout" wire:model.self="showCreatePlanModal" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Create {{ $category->name }} Plan</flux:heading>
                <flux:text class="mt-2">Create a new plan in the {{ $category->name }} category.</flux:text>
            </div>

            <form wire:submit="createPlan" class="space-y-6">
                <flux:input label="Plan Name" wire:model="createPlanForm.name" placeholder="e.g., Standard Service Plan" required />
                @error('createPlanForm.name')
                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                @enderror

                <flux:textarea label="Description" wire:model="createPlanForm.description" placeholder="Optional description of this plan..." rows="3" />
                @error('createPlanForm.description')
                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                @enderror

                <flux:input label="Color" wire:model="createPlanForm.color" type="color" />
                @error('createPlanForm.color')
                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                @enderror

                <flux:checkbox wire:model="createPlanForm.is_active" label="Active" />
                <flux:text size="sm" class="text-gray-600">Active plans are visible and can be used.</flux:text>

                <div class="flex">
                    <flux:spacer />
                    <flux:button variant="ghost" type="button" wire:click="$set('showCreatePlanModal', false)">Cancel</flux:button>
                    <flux:button variant="primary" type="submit">Create Plan</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

    <!-- Delete Plan Modal -->
    <flux:modal variant="flyout" wire:model.self="showDeletePlanModal" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Delete Plan</flux:heading>
                <flux:text class="mt-2">Are you sure you want to delete this plan? This action cannot be undone.</flux:text>
            </div>

            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                <p class="text-sm text-red-800 dark:text-red-400">
                    You are about to delete the plan <strong>"{{ $deletePlanForm['name'] }}"</strong>.
                </p>
            </div>

            <div class="flex">
                <flux:spacer />
                <flux:button variant="ghost" type="button" wire:click="$set('showDeletePlanModal', false)">Cancel</flux:button>
                <flux:button variant="danger" wire:click="deletePlan">Delete Plan</flux:button>
            </div>
        </div>
    </flux:modal>
</div>