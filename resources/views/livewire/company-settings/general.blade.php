<?php

use function Livewire\Volt\{state, mount};

state([
    'name' => '',
    'description' => '',
    'subdomain' => '',
    'isLoading' => false
]);

mount(function() {
    $company = auth()->user()->currentCompanyFromRequest() ?? auth()->user()->currentCompany();

    if ($company) {
        $this->name = $company->name;
        $this->description = $company->description ?? '';
        $this->subdomain = $company->subdomain;
    }
});

$save = function() {
    $this->isLoading = true;

    try {
        $company = auth()->user()->currentCompanyFromRequest() ?? auth()->user()->currentCompany();

        if ($company) {
            $company->update([
                'name' => $this->name,
                'description' => $this->description,
                'subdomain' => $this->subdomain,
            ]);

            $this->dispatch('company-updated');
        }
    } catch (\Exception $e) {
        // Handle error
    } finally {
        $this->isLoading = false;
    }
};

?>

<section class="w-full">
    @include('partials.settings-heading')

    <x-company-settings.layout :heading="__('Company Information')" :subheading="__('Update your company details and settings')">
        <form wire:submit="save" class="space-y-6">
            <!-- Company Name -->
            <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Company Name
                </label>
                <input type="text"
                       wire:model="name"
                       required
                       class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2"
                       placeholder="Acme Corporation">
            </div>

            <!-- Subdomain -->
            <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Subdomain
                </label>
                <div class="flex rounded-md shadow-sm">
                    <input type="text"
                           wire:model="subdomain"
                           required
                           class="flex-1 dark:bg-gray-900 dark:text-white rounded-l-md shadow-sm px-3 py-2 border-gray-300 dark:border-gray-700 focus:ring-blue-500 focus:border-blue-500">
                    <span class="inline-flex items-center px-3 rounded-r-md border border-l-0 border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-500 dark:text-gray-400 text-sm">
                        .{{ parse_url(config('app.url'), PHP_URL_HOST) }}
                    </span>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Only lowercase letters, numbers, and hyphens allowed
                </p>
            </div>

            <!-- Description -->
            <div class="space-y-2">
                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Description (Optional)
                </label>
                <textarea
                    wire:model="description"
                    id="description"
                    rows="3"
                    class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2"
                    placeholder="Brief description of your company..."
                ></textarea>
            </div>

            <div class="flex items-center justify-end">
                <button
                    type="submit"
                    disabled="{{ $isLoading }}"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150 disabled:opacity-50"
                >
                    @if($isLoading)
                        Saving...
                    @else
                        Save Changes
                    @endif
                </button>
            </div>
        </form>

        <div class="mt-4" wire:ignore>
            @if(session('company-updated'))
                <div class="p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                    <p class="text-sm text-green-800 dark:text-green-200">
                        Company information has been updated.
                    </p>
                </div>
            @endif
        </div>
    </x-company-settings.layout>
</section>
