@php
    // Determine avatar/initials to show
    if ($currentSite) {
        // For sites, show the company avatar/initials
        $company = $currentSite->company;
        $displayName = $currentSite->name;
        if ($company->avatar) {
            $avatarSrc = $company->getAvatarUrl();
            $avatarInitials = null;
        } else {
            $avatarSrc = null;
            $avatarInitials = substr($company->name, 0, 2);
        }
    } elseif ($currentCompany) {
        // For companies, show company avatar/initials
        $displayName = $currentCompany->name;
        if ($currentCompany->avatar) {
            $avatarSrc = $currentCompany->getAvatarUrl();
            $avatarInitials = null;
        } else {
            $avatarSrc = null;
            $avatarInitials = substr($currentCompany->name, 0, 2);
        }
    } else {
        // Fallback
        $displayName = 'Select Workspace';
        $avatarSrc = null;
        $avatarInitials = 'SW';
    }
@endphp

<div>
<flux:dropdown>
    @if($avatarSrc)
        <flux:profile
            avatar="{{ $avatarSrc }}"
            name="{{ $displayName }}"
            icon:trailing="chevron-up-down"
        />
    @else
        <flux:profile
            :initials="$avatarInitials"
            name="{{ $displayName }}"
            icon:trailing="chevron-up-down"
        />
    @endif
    <flux:navmenu>
        @if(count($userCompanies) > 0)
            <flux:menu.group heading="Companies">
                @foreach($userCompanies as $company)
                    @php
                        $isCurrent = $currentCompany && $currentCompany->id === $company->id && !$currentSite;
                    @endphp
                    @if($isCurrent)
                        <flux:navmenu.item
                            href="/app/company/{{ $company->id }}/dashboard"
                            icon="building-office"
                            wire:navigate
                            data-current="true"
                        >
                            {{ $company->name }}
                        </flux:navmenu.item>
                    @else
                        <flux:navmenu.item
                            href="/app/company/{{ $company->id }}/dashboard"
                            icon="building-office"
                            wire:navigate
                        >
                            {{ $company->name }}
                        </flux:navmenu.item>
                    @endif
                @endforeach
            </flux:menu.group>

            @foreach($userCompanies as $company)
                @php
                    $companySites = $userSites->where('company_id', $company->id);
                @endphp
                @if($companySites->count() > 0)
                    <flux:menu.group heading="{{ $company->name }} Sites">
                        @foreach($companySites as $site)
                            @php
                                $isCurrent = $currentSite && $currentSite->id === $site->id;
                            @endphp
                            @if($isCurrent)
                                <flux:navmenu.item
                                    href="/app/site/{{ $site->id }}/dashboard"
                                    icon="map-pin"
                                    wire:navigate
                                    data-current="true"
                                >
                                    {{ $site->name }}
                                </flux:navmenu.item>
                            @else
                                <flux:navmenu.item
                                    href="/app/site/{{ $site->id }}/dashboard"
                                    icon="map-pin"
                                    wire:navigate
                                >
                                    {{ $site->name }}
                                </flux:navmenu.item>
                            @endif
                        @endforeach
                    </flux:menu.group>
                @endif
            @endforeach
        @endif

        <flux:navmenu.item
            href="{{ route('view.select') }}"
            icon="arrows-pointing-out"
            wire:navigate
        >
            View All Workspaces
        </flux:navmenu.item>
    </flux:navmenu>
</flux:dropdown>
</div>

