<div class="contents">
    <div class="flex items-center justify-between w-full px-4" style="min-height: 3.5rem;">
        <span class="font-bold text-xl tracking-tight text-zinc-900 dark:text-white">Obsrv</span>
        <flux:sidebar.toggle class="lg:hidden self-center" icon="x-mark" style="height: 2rem; width: 2rem;" />
    </div>

    <flux:navlist variant="outline">
        <flux:navlist.item icon="home" :href="$company ? route('company.dashboard', ['company' => $company->id]) : '#'" :current="request()->routeIs('company.dashboard')" wire:navigate>{{ __('Dashboard') }}</flux:navlist.item>
        <flux:navlist.item icon="chart-bar" :href="$company ? route('company.monitoring', ['company' => $company->id]) : '#'" :current="request()->routeIs('company.monitoring')" wire:navigate>{{ __('Monitoring') }}</flux:navlist.item>

        <flux:navlist.group :heading="__('Support')" class="grid">
            <flux:navlist.item icon="chat-bubble-left-right" :href="$company ? route('company.tickets', ['company' => $company->id]) : '#'" :current="request()->routeIs('company.tickets*')" wire:navigate>{{ __('Tickets') }}</flux:navlist.item>
            <flux:navlist.item icon="document-text" :href="$company ? route('company.agreements', ['company' => $company->id]) : '#'" :current="request()->routeIs('company.agreements')" wire:navigate>{{ __('Agreements') }}</flux:navlist.item>
        </flux:navlist.group>

        <flux:navlist.group :heading="__('Company')" class="grid">
            <flux:navlist.item icon="users" :href="$company ? route('company.contacts', ['company' => $company->id]) : '#'" :current="request()->routeIs('company.contacts') || request()->routeIs('company.contact-groups')" wire:navigate>{{ __('Contacts') }}</flux:navlist.item>
            <flux:navlist.item icon="building-office" :href="$company ? route('company.sites', ['company' => $company->id]) : '#'" :current="request()->routeIs('company.sites') || request()->routeIs('company.site-groups')" wire:navigate>{{ __('Sites') }}</flux:navlist.item>
            <flux:navlist.item icon="credit-card" :href="$company ? route('company.service', ['company' => $company->id]) : '#'" :current="request()->routeIs('company.service')" wire:navigate>{{ __('Service Plans') }}</flux:navlist.item>
        </flux:navlist.group>
    </flux:navlist>

    <flux:spacer />

    @if($company && $company->isUserAdmin(auth()->user()))
        <flux:navlist variant="outline">
            <flux:navlist.group :heading="__('Company Admin')" class="grid">
                <flux:navlist.item icon="users" :href="$company ? route('company.users', ['company' => $company->id]) : '#'" :current="request()->routeIs('company.users') || request()->routeIs('company.teams')" wire:navigate>{{ __('Users') }}</flux:navlist.item>
                <flux:navlist.item icon="banknotes" :href="$company ? route('company.billing', ['company' => $company->id]) : '#'" :current="request()->routeIs('company.billing')" wire:navigate>{{ __('Billing') }}</flux:navlist.item>
                <flux:navlist.item icon="cog-6-tooth" :href="$company ? route('company.settings.profile', ['company' => $company->id]) : '#'" :current="request()->routeIs('company.settings*')" wire:navigate>
                    {{ __('Settings') }}
                </flux:navlist.item>
            </flux:navlist.group>
        </flux:navlist>
    @endif

    <!-- Desktop User Menu -->
    <flux:dropdown position="bottom" align="start">
        <flux:profile
            :name="auth()->user()->name"
            :initials="auth()->user()->initials()"
            icon:trailing="chevron-up-down"
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
