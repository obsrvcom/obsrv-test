<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    <!-- Header Section -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-neutral-200 dark:border-neutral-700 p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    Service Plans
                </h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">
                    Configure and manage your service plans, features, and pricing.
                </p>
            </div>
            <div class="flex gap-2">
                <flux:button size="sm" variant="ghost" wire:click="openCreateGroupModal">
                    <flux:icon name="plus" class="size-4" />
                    New Group
                </flux:button>
                <flux:button size="sm" variant="ghost" wire:click="openCreateCategoryModal">
                    <flux:icon name="squares-2x2" class="size-4" />
                    New Category
                </flux:button>
            </div>
        </div>

        <!-- Tab Navigation -->
        <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="-mb-px flex space-x-8">
                <button wire:click="$set('activeTab', 'overview')"
                        class="py-2 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'overview' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Overview
                </button>
                <button wire:click="$set('activeTab', 'groups')"
                        class="py-2 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'groups' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Plan Groups
                </button>
                <button wire:click="$set('activeTab', 'features')"
                        class="py-2 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'features' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Features
                </button>
            </nav>
        </div>
    </div>

    <!-- Content Section -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-neutral-200 dark:border-neutral-700 flex-1 p-6">
        @if($activeTab === 'overview')
            @include('livewire.company.service-plans.overview')
        @elseif($activeTab === 'groups')
            @include('livewire.company.service-plans.groups')
        @elseif($activeTab === 'features')
            @include('livewire.company.service-plans.features')
        @endif
    </div>

    <!-- Modals -->
    @include('livewire.company.service-plans.modals')
</div>
