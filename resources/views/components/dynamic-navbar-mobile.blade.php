@php
    $isUserSettings = request()->routeIs('settings.*') || str_starts_with(request()->path(), 'user-settings/');
    $isCompanySettings = (request()->routeIs('company.settings*') && !request()->routeIs('company.billing')) || str_starts_with(request()->path(), 'company-settings/');
    $isMonitoring = request()->routeIs('monitoring');

    // Get company from route model binding first (for company context pages)
    $routeCompany = request()->route('company');
    if ($routeCompany && is_object($routeCompany)) {
        $currentCompany = $routeCompany;
    } else {
        // Fallback to user's current company
        $currentCompany = auth()->user()->currentCompanyFromRequest() ?? auth()->user()->currentCompany();
    }
@endphp

@if($isCompanySettings)
    <flux:navbar.item :href="$currentCompany ? route('company.settings.profile', ['company' => $currentCompany->id]) : '#'" :current="request()->routeIs('company.settings.profile') || request()->routeIs('company.settings')" wire:navigate>Profile</flux:navbar.item>
    <flux:navbar.item :href="$currentCompany ? route('company.settings.chats', ['company' => $currentCompany->id]) : '#'" :current="request()->routeIs('company.settings.chats')" wire:navigate>Chats</flux:navbar.item>
@elseif($isUserSettings)
    <flux:navbar.item :href="route('settings.profile')" :current="request()->routeIs('settings.profile')" wire:navigate>Profile</flux:navbar.item>
    <flux:navbar.item :href="route('settings.password')" :current="request()->routeIs('settings.password')" wire:navigate>Password</flux:navbar.item>
    <flux:navbar.item :href="route('settings.appearance')" :current="request()->routeIs('settings.appearance')" wire:navigate>Appearance</flux:navbar.item>
    <flux:navbar.item :href="route('settings.devices')" :current="request()->routeIs('settings.devices')" wire:navigate>Devices</flux:navbar.item>
@elseif($isMonitoring)
    <x-site-selector />
@else
    <flux:navbar.item :href="$currentCompany ? route('company.dashboard', ['company' => $currentCompany->id]) : '#'" :current="request()->routeIs('company.dashboard')" wire:navigate>Dashboard</flux:navbar.item>
    <flux:navbar.item badge="32" href="#">Orders</flux:navbar.item>
    <flux:navbar.item href="#">Catalog</flux:navbar.item>
    <flux:navbar.item href="#">Configuration</flux:navbar.item>
@endif
