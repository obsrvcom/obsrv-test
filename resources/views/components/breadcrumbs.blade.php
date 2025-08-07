@php
    $route = request()->route();
    $routeName = $route->getName();
    $segments = [];

    // Determine context and build breadcrumbs
    if (str_starts_with($routeName, 'company.')) {
        $company = $route->parameter('company');
        if ($company) {


            // Then add home
            $segments[] = [
                'url' => route('app.index'),
                'label' => null,
                'icon' => 'home'
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
                $routeName === 'company.maintenance' => ['label' => 'Maintenance', 'url' => null],
                $routeName === 'company.appointments' => ['label' => 'Appointments', 'url' => null],
                $routeName === 'company.contacts' => ['label' => 'Contacts', 'url' => null],
                $routeName === 'company.contact-groups' => ['label' => 'Contact Groups', 'url' => null],
                $routeName === 'company.sites' => ['label' => 'Sites', 'url' => null],
                $routeName === 'company.site-groups' => ['label' => 'Site Groups', 'url' => null],
                $routeName === 'company.monitoring' => ['label' => 'Monitoring', 'url' => null],
                $routeName === 'company.teams' => ['label' => 'Teams', 'url' => null],
                $routeName === 'company.users' => ['label' => 'Users', 'url' => null],
                $routeName === 'company.plans' => ['label' => 'Plans', 'url' => null],
                str_starts_with($routeName, 'company.plans.category') => [
                    'label' => 'Plans',
                    'url' => route('company.plans', $company),
                    'children' => [
                        ['label' => $route->parameter('category')?->name . ' Plans' ?? 'Category Plans', 'url' => null]
                    ]
                ],
                str_starts_with($routeName, 'company.features.category') => [
                    'label' => 'Plans',
                    'url' => route('company.plans', $company),
                    'children' => [
                        ['label' => $route->parameter('category')?->name . ' Features' ?? 'Category Features', 'url' => null]
                    ]
                ],
                str_starts_with($routeName, 'company.plans.edit') => [
                    'label' => 'Plans',
                    'url' => route('company.plans', $company),
                    'children' => [
                        ['label' => $route->parameter('plan')?->name ?? 'Plan', 'url' => null]
                    ]
                ],
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

            // Then add home
            $segments[] = [
                'url' => route('app.index'),
                'label' => null,
                'icon' => 'home'
            ];

            // Handle special cases for ticket routes
            if ($routeName === 'site.tickets.view') {
                // For individual ticket view: Show Support -> Ticket Number
                $segments[] = [
                    'url' => route('site.tickets', $site),
                    'label' => 'Support',
                    'icon' => null
                ];

                $ticketId = $route->parameter('ticketId');
                // Try to get the actual ticket number
                try {
                    $ticket = \App\Models\Ticket::find($ticketId);
                    $ticketLabel = $ticket ? $ticket->ticket_number : "Ticket #{$ticketId}";
                } catch (\Exception $e) {
                    $ticketLabel = "Ticket #{$ticketId}";
                }

                $segments[] = [
                    'url' => null,
                    'label' => $ticketLabel,
                    'icon' => null
                ];
            } else {
                // Add current page for other routes
                $pageLabel = match($routeName) {
                    'site.dashboard' => 'Dashboard',
                    'site.tickets' => 'Support',
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
        }
    } elseif ($routeName === 'view.select') {
        // Add home first for view select
        $segments[] = [
            'url' => route('app.index'),
            'label' => null,
            'icon' => 'home'
        ];
        $segments[] = ['url' => null, 'label' => 'Select View', 'icon' => null];
    } elseif (str_starts_with($routeName, 'settings.')) {
        // Add home first for settings
        $segments[] = [
            'url' => route('app.index'),
            'label' => null,
            'icon' => 'home'
        ];

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
    } else {
        // Default: just home
        $segments[] = [
            'url' => route('app.index'),
            'label' => null,
            'icon' => 'home'
        ];
    }
@endphp

<livewire:header-view-selector />

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
