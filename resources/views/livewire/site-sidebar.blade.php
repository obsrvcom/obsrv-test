<div class="contents">
    <livewire:view-selector />

    @if($companyName)
        <div class="px-4 py-2 text-xs text-zinc-500 dark:text-zinc-400">
            Site managed by <span class="font-semibold">{{ $companyName }}</span>
        </div>
    @endif

    <flux:navlist variant="outline">
        <flux:navlist.item icon="home" :href="route('site.dashboard', ['site' => $siteId])" :current="request()->routeIs('site.dashboard')" wire:navigate>{{ __('Dashboard') }}</flux:navlist.item>
        <flux:navlist.item icon="chat-bubble-left-ellipsis" :href="route('site.ticket', ['site' => $siteId])" :current="request()->routeIs('site.ticket')" wire:navigate>{{ __('Support') }}</flux:navlist.item>
        <flux:navlist.item icon="calendar-days" :href="route('site.appointments', ['site' => $siteId])" :current="request()->routeIs('site.appointments')" wire:navigate>{{ __('Appointments') }}</flux:navlist.item>
        <flux:navlist.item icon="document-text" :href="route('site.quotations', ['site' => $siteId])" :current="request()->routeIs('site.quotations')" wire:navigate>{{ __('Quotations') }}</flux:navlist.item>
        <flux:navlist.item icon="wrench-screwdriver" :href="route('site.maintenance', ['site' => $siteId])" :current="request()->routeIs('site.maintenance')" wire:navigate>{{ __('Maintenance') }}</flux:navlist.item>
        <flux:navlist.item icon="chart-bar" :href="route('site.monitoring', ['site' => $siteId])" :current="request()->routeIs('site.monitoring')" wire:navigate>{{ __('Monitoring') }}</flux:navlist.item>
        <flux:navlist.item icon="key" :href="route('site.passwords', ['site' => $siteId])" :current="request()->routeIs('site.passwords')" wire:navigate>Passwords</flux:navlist.item>
        <flux:navlist.item icon="wifi" :href="route('site.internet', ['site' => $siteId])" :current="request()->routeIs('site.internet')" wire:navigate>{{ __('Internet') }}</flux:navlist.item>
        <flux:navlist.item icon="document-check" :href="route('site.agreement', ['site' => $siteId])" :current="request()->routeIs('site.agreement')" wire:navigate>{{ __('Agreement') }}</flux:navlist.item>
    </flux:navlist>

    <flux:spacer />

    <flux:navlist variant="outline">
        <flux:navlist.group :heading="__('Site Admin')" class="grid">
            <flux:navlist.item icon="users" :href="route('site.users', ['site' => $siteId])" :current="request()->routeIs('site.users')" wire:navigate>{{ __('Users') }}</flux:navlist.item>
            <flux:navlist.item icon="cog" :href="route('site.settings', ['site' => $siteId])" :current="request()->routeIs('site.settings')" wire:navigate>{{ __('Settings') }}</flux:navlist.item>
        </flux:navlist.group>
    </flux:navlist>

    <!-- Desktop User Menu -->
    <flux:dropdown position="bottom" align="start">
        <flux:profile
            :name="auth()->user()->name"
            :initials="auth()->user()->initials()"
            icon:trailing="chevrons-up-down"
        />

        <flux:menu class="w-[220px]">
            <flux:menu.radio.group>
                <div class="p-0 text-sm font-normal">
                    <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                        <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                            <span
                                class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
                            >
                                {{ auth()->user()->initials() }}
                            </span>
                        </span>

                        <div class="grid flex-1 text-start text-sm leading-tight">
                            <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                            <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                        </div>
                    </div>
                </div>
            </flux:menu.radio.group>

            <flux:menu.separator />

            <flux:menu.radio.group>
                <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>{{ __('User Settings') }}</flux:menu.item>
            </flux:menu.radio.group>

            <flux:menu.separator />

            <form method="POST" action="{{ route('logout') }}" class="w-full">
                @csrf
                <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                    {{ __('Log Out') }}
                </flux:menu.item>
            </form>
        </flux:menu>
    </flux:dropdown>
</div>
