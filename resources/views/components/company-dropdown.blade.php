@php
    $currentCompany = auth()->user()->currentCompanyFromRequest() ?? auth()->user()->currentCompany();
    $userCompanies = auth()->user()->companies;

        // Get the configured app domain from environment
    $appUrl = config('app.url');
    $appDomain = parse_url($appUrl, PHP_URL_HOST);
@endphp

<flux:dropdown position="bottom" align="start">
    <button type="button" class="flex h-12 w-full items-center justify-between rounded-lg text-zinc-500 hover:bg-zinc-800/5 hover:text-zinc-800 lg:h-10 dark:text-white/80 dark:hover:bg-white/[7%] dark:hover:text-white ps-1 pe-2 py-5 cursor-pointer focus:outline-none">
        <div class="flex items-center gap-2">
            <div class="flex aspect-square size-8 items-center justify-center rounded-md bg-blue-600 text-white flex-shrink-0">
                <span class="text-sm font-semibold">
                    {{ $currentCompany ? substr($currentCompany->name, 0, 2) : '?' }}
                </span>
            </div>
            <span class="truncate leading-tight font-semibold text-sm">
                {{ $currentCompany ? $currentCompany->name : 'No Company' }}
            </span>
        </div>
        <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
    </button>

    <flux:menu class="w-[220px]">
        <div class="p-1.5">
            @foreach($userCompanies as $company)
                @if($currentCompany && $company->id === $currentCompany->id)
                    <div class="flex items-center gap-2.5 p-2 rounded-md bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 font-medium cursor-default">
                        <div class="flex aspect-square size-7 items-center justify-center rounded-md bg-blue-600 text-white">
                            <span class="text-xs font-semibold">{{ substr($company->name, 0, 2) }}</span>
                        </div>
                        <span class="truncate text-sm">{{ $company->name }}</span>
                    </div>
                @else
                    <a href="http://{{ $company->subdomain }}.{{ $appDomain }}" class="flex items-center gap-2.5 p-2 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                        <div class="flex aspect-square size-7 items-center justify-center rounded-md bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300">
                            <span class="text-xs font-semibold">{{ substr($company->name, 0, 2) }}</span>
                        </div>
                        <span class="truncate text-sm">{{ $company->name }}</span>
                    </a>
                @endif
            @endforeach
        </div>
    </flux:menu>
</flux:dropdown>
