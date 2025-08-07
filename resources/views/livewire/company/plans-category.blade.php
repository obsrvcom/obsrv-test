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
            <div class="flex gap-2">
                <flux:button size="sm" variant="ghost" href="{{ route('company.features.category', ['company' => $company, 'category' => $category]) }}">
                    <flux:icon name="cog-6-tooth" class="size-4" />
                    Manage Features
                </flux:button>
                <flux:button wire:click="openCreatePlanModal" variant="primary">
                    <flux:icon name="plus" class="size-4" />
                    Add Plan
                </flux:button>
            </div>
        </div>
    </div>

    <!-- Plans Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
        @foreach($plans as $plan)
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-neutral-200 dark:border-neutral-700 p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="p-2 rounded-lg" style="background-color: {{ $plan->color }}20;">
                            <flux:icon name="document-text" class="size-6" style="color: {{ $plan->color }}" />
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 dark:text-white">{{ $plan->name }}</h3>
                            @if(!$plan->is_active)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300">
                                    Inactive
                                </span>
                            @endif
                        </div>
                    </div>
                    <flux:dropdown>
                        <flux:button size="sm" variant="ghost" icon="ellipsis-horizontal" />
                        <flux:menu>
                            <flux:menu.item icon="pencil" href="{{ route('company.plans.edit', ['company' => $company, 'plan' => $plan]) }}">
                                Edit Plan
                            </flux:menu.item>
                            <flux:menu.item icon="document-duplicate">
                                Duplicate Plan
                            </flux:menu.item>
                            <flux:menu.separator />
                            <flux:menu.item icon="trash" variant="danger">
                                Delete Plan
                            </flux:menu.item>
                        </flux:menu>
                    </flux:dropdown>
                </div>

                @if($plan->description)
                    <p class="text-gray-600 dark:text-gray-400 text-sm mb-4">{{ $plan->description }}</p>
                @endif

                <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-3 mb-4">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $plan->revisions_count }}</div>
                        <div class="text-xs text-gray-500">Revisions</div>
                    </div>
                </div>

                @if($plan->revisions->count() > 0)
                    @php $latestRevision = $plan->revisions->first(); @endphp
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-3 mb-4">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600 dark:text-gray-400">Latest:</span>
                            <div class="flex items-center gap-2">
                                <span class="font-medium">{{ $latestRevision->name }}</span>
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium
                                    {{ $latestRevision->status === 'published' ? 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-300' : '' }}
                                    {{ $latestRevision->status === 'draft' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-300' : '' }}
                                    {{ $latestRevision->status === 'archived' ? 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300' : '' }}">
                                    {{ ucfirst($latestRevision->status) }}
                                </span>
                            </div>
                        </div>
                    </div>
                @endif

                <flux:button href="{{ route('company.plans.edit', ['company' => $company, 'plan' => $plan]) }}" size="sm" variant="ghost" class="w-full">
                    <flux:icon name="pencil" class="size-4" />
                    Configure Plan
                </flux:button>
            </div>
        @endforeach

        @if($plans->count() === 0)
            <div class="col-span-full text-center py-12">
                <flux:icon name="document-plus" class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No plans yet</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Create your first {{ strtolower($category->name) }} plan to get started.
                </p>
                <div class="mt-6">
                    <flux:button wire:click="openCreatePlanModal" size="sm">
                        <flux:icon name="plus" class="size-4" />
                        Create Plan
                    </flux:button>
                </div>
            </div>
        @endif
    </div>

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
</div>