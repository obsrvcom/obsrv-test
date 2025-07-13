<?php

use function Livewire\Volt\{state, mount};

state([
    'currentPlan' => 'Free',
    'nextBillingDate' => null,
    'usage' => [
        'members' => 0,
        'storage' => '0 MB',
        'api_calls' => 0
    ]
]);

mount(function() {
    $company = auth()->user()->currentCompanyFromRequest() ?? auth()->user()->currentCompany();

    if ($company) {
        // Load actual usage data here when implemented
        $this->usage['members'] = $company->users()->count();
    }
});

?>

<section class="w-full">
    @include('partials.settings-heading')

    <x-company-settings.layout :heading="__('Billing & Usage')" :subheading="__('Manage your subscription and view usage statistics')">
        <div class="space-y-6">
            <!-- Current Plan -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Current Plan</h3>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $currentPlan }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Plan</p>
                    </div>
                    <button class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-25 transition ease-in-out duration-150">
                        Upgrade Plan
                    </button>
                </div>
            </div>

            <!-- Usage Statistics -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Usage This Month</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $usage['members'] }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Team Members</p>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $usage['storage'] }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Storage Used</p>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($usage['api_calls']) }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">API Calls</p>
                    </div>
                </div>
            </div>

            <!-- Billing Information -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Billing Information</h3>
                <div class="space-y-4">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Next billing date:</span>
                        <span class="text-sm text-gray-900 dark:text-white">
                            {{ $nextBillingDate ? $nextBillingDate->format('M j, Y') : 'N/A' }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Payment method:</span>
                        <span class="text-sm text-gray-900 dark:text-white">Not configured</span>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <button class="inline-flex items-center px-3 py-1.5 border border-gray-300 dark:border-gray-700 rounded-md text-xs font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-offset-gray-800">
                        Update Payment Method
                    </button>
                </div>
            </div>

            <!-- Invoice History -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Invoice History</h3>
                <div class="text-center py-8">
                    <div class="mx-auto h-12 w-12 text-gray-400">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No invoices yet</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Invoices will appear here once you have billing activity.
                    </p>
                </div>
            </div>
        </div>
    </x-company-settings.layout>
</section>
