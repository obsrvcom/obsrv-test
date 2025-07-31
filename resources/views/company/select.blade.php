@php
        // Get the configured app domain from environment
    $appUrl = config('app.url');
    $appDomain = parse_url($appUrl, PHP_URL_HOST);
@endphp

<x-layouts.auth :title="__('Select Company')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Select Company')" :description="__('Choose which company you\'d like to work with')" />

        @if(session('error'))
            <div class="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                <p class="text-sm text-red-800 dark:text-red-200">
                    {{ session('error') }}
                </p>
            </div>
        @endif

        <div class="space-y-3">
            @foreach($companies as $company)
                <div class="block">
                    <a href="{{ route('company.dashboard', ['company' => $company->id]) }}" class="w-full p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors text-left block">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="font-medium text-gray-900 dark:text-white">
                                    {{ $company->name }}
                                </h3>
                                @if($company->description)
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                        {{ $company->description }}
                                    </p>
                                @endif
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                                    Company ID: {{ $company->id }}
                                </p>
                            </div>
                            <div class="flex items-center space-x-2">
                                @if($company->pivot->role === 'owner')
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                                        Owner
                                    </span>
                                @elseif($company->pivot->role === 'admin')
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                        Admin
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200">
                                        Member
                                    </span>
                                @endif
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>

        <div class="pt-6 border-t border-gray-200 dark:border-gray-700">
            <flux:button
                variant="outline"
                href="{{ route('company.create') }}"
                class="w-full"
            >
                Create New Company
            </flux:button>
        </div>
    </div>
</x-layouts.auth>
