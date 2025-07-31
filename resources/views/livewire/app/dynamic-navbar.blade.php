<nav>
    @if($isCompanySettings)
        <flux:navbar scrollable>
            <!-- Company Settings Sub-Navigation -->
            <flux:navbar.item icon="user" :href="$currentCompany ? route('company.settings.profile', ['company' => $currentCompany->id]) : '#'" :current="request()->routeIs('company.settings.profile') || request()->routeIs('company.settings')" wire:navigate>
                {{ __('Profile') }}
            </flux:navbar.item>
            <flux:navbar.item icon="chat-bubble-left-right" :href="$currentCompany ? route('company.settings.chats', ['company' => $currentCompany->id]) : '#'" :current="request()->routeIs('company.settings.chats')" wire:navigate>
                {{ __('Chats') }}
            </flux:navbar.item>
        </flux:navbar>
    @elseif($isUserSettings)
        <flux:navbar scrollable>
            <!-- User Settings Sub-Navigation -->
            <flux:navbar.item icon="user" :href="route('settings.profile')" :current="request()->routeIs('settings.profile')" wire:navigate>
                {{ __('Profile') }}
            </flux:navbar.item>
            <flux:navbar.item icon="key" :href="route('settings.password')" :current="request()->routeIs('settings.password')" wire:navigate>
                {{ __('Password') }}
            </flux:navbar.item>
            <flux:navbar.item icon="sun" :href="route('settings.appearance')" :current="request()->routeIs('settings.appearance')" wire:navigate>
                {{ __('Appearance') }}
            </flux:navbar.item>
            <flux:navbar.item icon="device-phone-mobile" :href="route('settings.devices')" :current="request()->routeIs('settings.devices')" wire:navigate>
                {{ __('Devices') }}
            </flux:navbar.item>
        </flux:navbar>
    @endif
</nav>
