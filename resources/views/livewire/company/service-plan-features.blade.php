<div class="flex h-full w-full flex-1 flex-col">
    <!-- Navigation -->
    <div class="bg-gray-50 dark:bg-gray-800 border-b border-neutral-200 dark:border-neutral-700 px-4 flex items-center gap-4">
        <flux:badge color="zinc">Service Plans</flux:badge>

        <flux:navbar>
            <flux:navbar.item
                :href="route('company.service', ['company' => $company->id])"
                icon="clipboard-document-list"
                wire:navigate
            >
                Service Plans
            </flux:navbar.item>
            <flux:navbar.item
                :href="route('company.service.features', ['company' => $company->id])"
                :current="true"
                icon="cog-6-tooth"
                wire:navigate
            >
                Features
            </flux:navbar.item>
        </flux:navbar>
    </div>

    <div class="p-4 flex flex-col gap-4 h-0 flex-1">
        <!-- Header -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-neutral-200 dark:border-neutral-700 p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Features</h1>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">Manage feature groups and their individual features.</p>
                </div>
                <div class="flex gap-2">
                    <flux:button size="sm" variant="ghost" wire:click="openCreateFeatureGroupModal">
                        <flux:icon name="plus" class="size-4" />
                        New Feature Group
                    </flux:button>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-neutral-200 dark:border-neutral-700 flex-1 overflow-hidden">
            <div class="h-full overflow-y-auto p-4">
                @include('livewire.company.service-plans-new.feature-groups')
            </div>
        </div>

        <!-- Modals -->
        @include('livewire.company.service-plan-features.modals')
    </div>
</div>
