<?php

use function Livewire\Volt\{state, mount, layout, on};
use Illuminate\Support\Facades\Auth;

layout('components.layouts.company');

state([
    'selectedSiteId' => null,
    'sites' => [],
]);

$loadSites = function () {
    $company = Auth::user()->currentCompanyFromRequest() ?? Auth::user()->currentCompany();
    $this->sites = $company ? $company->sites()->orderBy('name')->get() : collect();
};

$setSelectedSite = function ($siteId) {
    $this->selectedSiteId = $siteId;
};

on(['site-selected' => function ($siteId) {
    $this->selectedSiteId = $siteId['siteId'];
}]);

mount(function () {
    $this->loadSites();
});

?>

<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    <!-- Header Section -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-neutral-200 dark:border-neutral-700 p-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ __('Monitoring') }}
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">
                {{ __('Monitor your sites and track performance metrics.') }}
            </p>
        </div>
    </div>

    <!-- Monitoring Content -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-neutral-200 dark:border-neutral-700 flex-1">
        @if($sites->count() === 0)
            <div class="text-center py-16">
                <div class="mx-auto h-16 w-16 rounded-full bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center">
                    <flux:icon name="chart-bar" class="h-8 w-8 text-neutral-400" />
                </div>
                <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">{{ __('No sites to monitor') }}</h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 max-w-sm mx-auto">
                    {{ __('Create some sites first to start monitoring their performance.') }}
                </p>
                <div class="mt-6">
                    <flux:button
                        variant="primary"
                        icon="building-office"
                        :href="route('sites')"
                    >
                        {{ __('Go to Sites') }}
                    </flux:button>
                </div>
            </div>
        @elseif(!$selectedSiteId)
            <div class="text-center py-16">
                <div class="mx-auto h-16 w-16 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                    <flux:icon name="chart-bar" class="h-8 w-8 text-blue-600 dark:text-blue-400" />
                </div>
                <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">{{ __('Select a site to monitor') }}</h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 max-w-sm mx-auto">
                    {{ __('Choose a site from the dropdown above to view its monitoring data.') }}
                </p>
            </div>
        @else
            @php $selectedSite = $sites->firstWhere('id', $selectedSiteId); @endphp
            @if($selectedSite)
                <div class="p-6">
                    <div class="mb-6">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                            {{ __('Monitoring Dashboard') }} - {{ $selectedSite->name }}
                        </h2>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                            {{ $selectedSite->address }}
                        </p>
                    </div>

                <!-- Placeholder for monitoring content -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="bg-neutral-50 dark:bg-neutral-900 rounded-lg p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <flux:icon name="signal" class="h-6 w-6 text-green-600 dark:text-green-400" />
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">Status</p>
                                <p class="text-2xl font-bold text-green-600 dark:text-green-400">Online</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-neutral-50 dark:bg-neutral-900 rounded-lg p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <flux:icon name="clock" class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">Uptime</p>
                                <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">99.9%</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-neutral-50 dark:bg-neutral-900 rounded-lg p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <flux:icon name="bolt" class="h-6 w-6 text-yellow-600 dark:text-yellow-400" />
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">Response Time</p>
                                <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">245ms</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Chart placeholder -->
                <div class="mt-8">
                    <div class="bg-neutral-50 dark:bg-neutral-900 rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Performance Over Time</h3>
                        <div class="h-64 bg-white dark:bg-gray-800 rounded border border-neutral-200 dark:border-neutral-700 flex items-center justify-center">
                            <div class="text-center">
                                <flux:icon name="chart-bar" class="h-12 w-12 text-neutral-400 mx-auto mb-2" />
                                <p class="text-sm text-neutral-500 dark:text-neutral-400">Chart placeholder</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        @endif
    </div>
</div>
