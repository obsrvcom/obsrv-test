@php
    $route = request()->route();
    $routeName = $route->getName();
    $segments = [];

    // Start with home
    $segments[] = [
        'url' => route('app.index'),
        'label' => null,
        'icon' => 'home'
    ];

    // Determine context and build breadcrumbs
    if (str_starts_with($routeName, 'company.')) {
        $company = $route->parameter('company');
        if ($company) {
            // Add company context
            $segments[] = [
                'url' => route('company.dashboard', $company),
                'label' => $company->name,
                'icon' => null
            ];

            // Add current page
            $pageSegment = match(true) {
                $routeName === 'company.dashboard' => ['label' => 'Dashboard', 'url' => null],
                $routeName === 'company.tickets' => ['label' => 'Tickets', 'url' => null],
                str_starts_with($routeName, 'company.tickets.') => [
                    'label' => 'Tickets',
                    'url' => route('company.tickets', $company),
                    'children' => [
                        ['label' => $route->parameter('site')?->name ?? 'Site', 'url' => null]
                    ]
                ],
                $routeName === 'company.agreements' => ['label' => 'Agreements', 'url' => null],
                $routeName === 'company.contacts' => ['label' => 'Contacts', 'url' => null],
                $routeName === 'company.contact-groups' => ['label' => 'Contact Groups', 'url' => null],
                $routeName === 'company.sites' => ['label' => 'Sites', 'url' => null],
                $routeName === 'company.site-groups' => ['label' => 'Site Groups', 'url' => null],
                $routeName === 'company.monitoring' => ['label' => 'Monitoring', 'url' => null],
                $routeName === 'company.teams' => ['label' => 'Teams', 'url' => null],
                $routeName === 'company.users' => ['label' => 'Users', 'url' => null],
                $routeName === 'company.service' => ['label' => 'Service Plans', 'url' => null],
                $routeName === 'company.billing' => ['label' => 'Billing', 'url' => null],
                str_starts_with($routeName, 'company.settings') => [
                    'label' => 'Settings',
                    'url' => route('company.settings.profile', $company),
                    'children' => [
                        ['label' => match($routeName) {
                            'company.settings.profile' => 'Profile',
                            'company.settings.members' => 'Members',
                            'company.settings.chats' => 'Chats',
                            default => 'Settings'
                        }, 'url' => null]
                    ]
                ],
                default => ['label' => 'Page', 'url' => null]
            };

            if (isset($pageSegment['children'])) {
                $segments[] = ['url' => $pageSegment['url'], 'label' => $pageSegment['label'], 'icon' => null];
                foreach ($pageSegment['children'] as $child) {
                    $segments[] = ['url' => $child['url'], 'label' => $child['label'], 'icon' => null];
                }
            } else {
                $segments[] = ['url' => $pageSegment['url'], 'label' => $pageSegment['label'], 'icon' => null];
            }
        }
    } elseif (str_starts_with($routeName, 'site.')) {
        $site = $route->parameter('site');
        if ($site) {
            // Add site context
            $segments[] = [
                'url' => route('site.dashboard', $site),
                'label' => $site->name,
                'icon' => null
            ];

            // Add current page
            $pageLabel = match($routeName) {
                'site.dashboard' => 'Dashboard',
                'site.ticket' => 'Support',
                'site.appointments' => 'Appointments',
                'site.quotations' => 'Quotations',
                'site.maintenance' => 'Maintenance',
                'site.monitoring' => 'Monitoring',
                'site.passwords' => 'Passwords',
                'site.internet' => 'Internet',
                'site.agreement' => 'Agreement',
                'site.users' => 'Users',
                'site.settings' => 'Settings',
                default => 'Page'
            };

            if ($routeName !== 'site.dashboard') {
                $segments[] = ['url' => null, 'label' => $pageLabel, 'icon' => null];
            }
        }
    } elseif ($routeName === 'view.select') {
        $segments[] = ['url' => null, 'label' => 'Select View', 'icon' => null];
    } elseif (str_starts_with($routeName, 'settings.')) {
        $segments[] = [
            'url' => route('settings.profile'),
            'label' => 'Settings',
            'icon' => null
        ];

        $pageLabel = match($routeName) {
            'settings.profile' => 'Profile',
            'settings.password' => 'Password',
            'settings.appearance' => 'Appearance',
            'settings.devices' => 'Devices',
            default => 'Settings'
        };

        if ($routeName !== 'settings.profile') {
            $segments[] = ['url' => null, 'label' => $pageLabel, 'icon' => null];
        }
    }
@endphp

<flux:breadcrumbs>
    @foreach($segments as $index => $segment)
        @if($segment['icon'])
            <flux:breadcrumbs.item
                :href="$segment['url']"
                icon="{{ $segment['icon'] }}"
                separator="slash"
            />
        @else
            <flux:breadcrumbs.item
                :href="$segment['url']"
                separator="slash"
            >
                {{ $segment['label'] }}
            </flux:breadcrumbs.item>
        @endif
    @endforeach
</flux:breadcrumbs>
