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
                    {{ __('Company Dashboard') }}
                </h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">
                    {{ __('Welcome to your company dashboard.') }}
                </p>
            </div>
        </div>
    </div>

    <!-- Dashboard Content -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-neutral-200 dark:border-neutral-700 flex-1 p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Quick Stats or Dashboard Widgets will go here -->
            <div class="bg-neutral-50 dark:bg-neutral-900/50 rounded-lg p-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Sites</h3>
                <p class="text-gray-600 dark:text-gray-400 text-sm">
                    Manage your business locations and sites.
                </p>
            </div>

            <div class="bg-neutral-50 dark:bg-neutral-900/50 rounded-lg p-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Contacts</h3>
                <p class="text-gray-600 dark:text-gray-400 text-sm">
                    View and manage business contacts.
                </p>
            </div>

            <div class="bg-neutral-50 dark:bg-neutral-900/50 rounded-lg p-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Issues</h3>
                <p class="text-gray-600 dark:text-gray-400 text-sm">
                    Track and resolve reported issues.
                </p>
            </div>

            <div class="bg-neutral-50 dark:bg-neutral-900/50 rounded-lg p-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Tasks</h3>
                <p class="text-gray-600 dark:text-gray-400 text-sm">
                    Manage and track assignments.
                </p>
            </div>
        </div>
    </div>
</div>
