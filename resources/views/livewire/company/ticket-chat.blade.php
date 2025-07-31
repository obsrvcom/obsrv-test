<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    <!-- Header Section -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-neutral-200 dark:border-neutral-700 p-6">
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center gap-4 mb-2">
                    <a href="{{ route('company.tickets', ['company' => $company->id]) }}"
                       class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <flux:icon name="arrow-left" class="h-4 w-4 mr-2" />
                        Back to Tickets
                    </a>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ $site->name }}
                </h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">
                    {{ __('Support Chat') }}
                </p>
            </div>
        </div>
    </div>

    <!-- Ticket Chat Content -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-neutral-200 dark:border-neutral-700 flex-1 p-6">
        <div class="text-center py-16">
            <div class="mx-auto h-16 w-16 rounded-full bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center">
                <flux:icon name="chat-bubble-left-right" class="h-8 w-8 text-neutral-400" />
            </div>
            <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">{{ __('Support Chat Coming Soon') }}</h3>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 max-w-sm mx-auto">
                {{ __('We\'re working on an improved support chat system for this site. This feature will be available soon.') }}
            </p>
        </div>
    </div>
</div>

