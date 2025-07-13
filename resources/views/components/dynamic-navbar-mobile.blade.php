@php
    $isUserSettings = request()->routeIs('settings.*') || str_starts_with(request()->path(), 'user-settings/');
    $isCompanySettings = request()->routeIs('company.settings*') || str_starts_with(request()->path(), 'company-settings/');
@endphp

@if($isCompanySettings)
    <flux:navbar.item :href="route('company.settings')" :current="request()->routeIs('company.settings')" wire:navigate>General</flux:navbar.item>
    <flux:navbar.item :href="route('company.settings.members')" :current="request()->routeIs('company.settings.members')" wire:navigate>Members</flux:navbar.item>
    <flux:navbar.item :href="route('company.settings.billing')" :current="request()->routeIs('company.settings.billing')" wire:navigate>Billing</flux:navbar.item>
@elseif($isUserSettings)
    <flux:navbar.item :href="route('settings.profile')" :current="request()->routeIs('settings.profile')" wire:navigate>Profile</flux:navbar.item>
    <flux:navbar.item :href="route('settings.password')" :current="request()->routeIs('settings.password')" wire:navigate>Password</flux:navbar.item>
    <flux:navbar.item :href="route('settings.appearance')" :current="request()->routeIs('settings.appearance')" wire:navigate>Appearance</flux:navbar.item>
    <flux:navbar.item :href="route('settings.devices')" :current="request()->routeIs('settings.devices')" wire:navigate>Devices</flux:navbar.item>
@else
    <flux:navbar.item :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>Dashboard</flux:navbar.item>
    <flux:navbar.item badge="32" href="#">Orders</flux:navbar.item>
    <flux:navbar.item href="#">Catalog</flux:navbar.item>
    <flux:navbar.item href="#">Configuration</flux:navbar.item>
@endif
