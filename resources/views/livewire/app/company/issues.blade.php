<?php

use function Livewire\Volt\{state, layout};

layout('components.layouts.company');

?>

<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    <!-- Header Section -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-neutral-200 dark:border-neutral-700 p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ __('Issues') }}
                </h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">
                    {{ __('Track and manage reported issues and bugs.') }}
                </p>
            </div>
        </div>
    </div>

    <!-- Issues Content -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-neutral-200 dark:border-neutral-700 flex-1 p-6">
        <div class="text-center py-16">
            <div class="mx-auto h-16 w-16 rounded-full bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center">
                <flux:icon name="exclamation-triangle" class="h-8 w-8 text-neutral-400" />
            </div>
            <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">{{ __('No issues yet') }}</h3>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 max-w-sm mx-auto">
                {{ __('Issue tracking functionality will be implemented here.') }}
            </p>
        </div>
    </div>
</div>
