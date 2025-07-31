<flux:dropdown>
    <flux:profile
        icon:trailing="chevron-up-down"
        name="{{ $currentSite ? $currentSite->name : ($currentCompany ? $currentCompany->name : '') }}"
    />
    <flux:popover class="w-full max-w-[calc(100vw-2rem)] sm:max-w-[30rem] flex flex-col gap-4">
        <div>
            @if(count($userCompanies) > 0)
                <div class="mb-2 font-semibold">Companies</div>
                <ul class="mb-4">
                    @foreach($userCompanies as $company)
                        <li>
                            <a href="/app/company/{{ $company->id }}/dashboard" class="hover:underline flex items-center gap-2">
                                <span class="font-medium">{{ $company->name }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
            <div class="mb-2 font-semibold">Sites</div>
            <ul class="mb-4">
                @foreach($userSites as $site)
                    <li>
                        <a href="/app/site/{{ $site->id }}/dashboard" class="hover:underline flex items-center gap-2">
                            <span class="font-medium">{{ $site->name }}</span>
                            <span class="text-xs text-gray-500">({{ $site->company->name }})</span>
                        </a>
                    </li>
                @endforeach
            </ul>

            <!-- Separator and Full Selector Button -->
            <div class="border-t border-gray-200 dark:border-gray-700 pt-3">
                <flux:button
                    href="{{ route('view.select') }}"
                    variant="outline"
                    size="sm"
                    class="w-full justify-center"
                    icon="arrows-pointing-out"
                    wire:navigate
                >
                    View All Workspaces
                </flux:button>
            </div>
        </div>
    </flux:popover>
</flux:dropdown>
