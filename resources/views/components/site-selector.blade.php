@php
    $company = auth()->user()->currentCompanyFromRequest() ?? auth()->user()->currentCompany();
    $sites = $company ? $company->sites()->orderBy('name')->get() : collect();
@endphp

<div class="w-52">
    @if($sites->count() > 0)
        <flux:dropdown position="bottom" align="start">
            <flux:button variant="ghost" icon="building-office" icon:trailing="chevron-down" class="justify-start text-left font-medium w-full">
                {{ __('Select Site') }}
            </flux:button>
            <flux:menu class="w-64">
                @foreach($sites as $site)
                    <flux:menu.item
                        wire:click="$dispatch('site-selected', { siteId: {{ $site->id }} })"
                    >
                        {{ $site->name }}
                    </flux:menu.item>
                @endforeach
            </flux:menu>
        </flux:dropdown>
    @else
        <flux:button variant="ghost" icon="building-office" class="justify-start text-left font-medium text-gray-500 w-full" disabled>
            {{ __('No Sites Available') }}
        </flux:button>
    @endif
</div>
