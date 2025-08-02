<div class="flex h-full w-full flex-1 flex-col gap-4">
    <!-- Header -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-neutral-200 dark:border-neutral-700 p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    Service Plans
                </h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">
                    Create and manage service plans with revisions and levels.
                </p>
            </div>
            <div class="flex gap-2">
                <flux:button size="sm" variant="ghost" wire:click="openCreatePlanModal">
                    <flux:icon name="plus" class="size-4" />
                    New Plan
                </flux:button>
            </div>
        </div>
    </div>

    <!-- Plans List -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-neutral-200 dark:border-neutral-700 flex-1 overflow-hidden">
        <div class="h-full overflow-y-auto p-6">
            @include('livewire.company.service-plans-new.list')
        </div>
    </div>

    <!-- Modals -->
    @include('livewire.company.service-plans-new.modals')
</div>
