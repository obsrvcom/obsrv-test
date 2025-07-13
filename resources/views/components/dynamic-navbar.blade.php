@php
    $isUserSettings = request()->routeIs('settings.*') || str_starts_with(request()->path(), 'user-settings/');
    $isCompanySettings = request()->routeIs('company.settings*') || str_starts_with(request()->path(), 'company-settings/');
@endphp

@if($isCompanySettings)
    <!-- Company Settings Sub-Navigation -->
    <flux:navbar.item icon="cog-6-tooth" :href="route('company.settings')" :current="request()->routeIs('company.settings')" wire:navigate>
        {{ __('General') }}
    </flux:navbar.item>
    <flux:navbar.item icon="users" :href="route('company.settings.members')" :current="request()->routeIs('company.settings.members')" wire:navigate>
        {{ __('Members') }}
    </flux:navbar.item>
    <flux:navbar.item icon="credit-card" :href="route('company.settings.billing')" :current="request()->routeIs('company.settings.billing')" wire:navigate>
        {{ __('Billing') }}
    </flux:navbar.item>
@elseif($isUserSettings)
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
@else
    <!-- Default Dashboard Sub-Navigation -->
    <flux:navbar.item icon="layout-grid" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
        {{ __('Dashboard') }}
    </flux:navbar.item>
    <flux:navbar.item icon="shopping-cart" href="#" badge="32">
        {{ __('Orders') }}
    </flux:navbar.item>
    <flux:navbar.item icon="cube" href="#">
        {{ __('Catalog') }}
    </flux:navbar.item>
    <flux:navbar.item icon="cog" href="#">
        {{ __('Configuration') }}
    </flux:navbar.item>
@endif
