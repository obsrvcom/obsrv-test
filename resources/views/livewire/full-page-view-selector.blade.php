<div class=" bg-gray-50 dark:bg-gray-900 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="w-full max-w-lg">
        <div class="text-center mb-6">
            <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                Select your workspace
            </h2>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Choose a company or site to continue
            </p>
        </div>

        <div class="w-full">
            <div class="bg-white dark:bg-gray-800 px-6 py-6 shadow rounded-lg">
                <div class="space-y-4">
                    @if($userCompanies->count() > 0)
                        <div>
                            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-4">
                                Companies
                            </h3>
                            <div class="space-y-3">
                                @foreach($userCompanies as $company)
                                    <button
                                        wire:click="selectCompany({{ $company->id }})"
                                        class="w-full flex items-center justify-between p-4 text-left border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    >
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                                                <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                                </svg>
                                            </div>
                                            <div class="ml-4">
                                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                                    {{ $company->name }}
                                                </p>
                                                @if($company->description)
                                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                                        {{ $company->description }}
                                                    </p>
                                                @endif
                                            </div>
                                        </div>
                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($userSites->count() > 0)
                        <div>
                            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-4">
                                Sites
                            </h3>
                            <div class="space-y-3">
                                @foreach($userSites as $site)
                                    <button
                                        wire:click="selectSite({{ $site->id }})"
                                        class="w-full flex items-center justify-between p-4 text-left border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    >
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                                                <svg class="h-5 w-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                </svg>
                                            </div>
                                            <div class="ml-4">
                                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                                    {{ $site->name }}
                                                </p>
                                                @if($site->company)
                                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                                        {{ $site->company->name }}
                                                    </p>
                                                @endif
                                            </div>
                                        </div>
                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($userCompanies->count() === 0 && $userSites->count() === 0)
                        <div class="text-center py-6">
                            <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-2">
                                No workspace access
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
                                You don't have access to any companies or sites yet.
                            </p>
                            <flux:button
                                href="{{ route('company.select') }}"
                                variant="outline"
                                wire:navigate
                            >
                                Create or Join Company
                            </flux:button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
