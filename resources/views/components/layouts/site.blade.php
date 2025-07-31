<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    @include('partials.head')
</head>
<body class="min-h-screen bg-white dark:bg-zinc-800">
    <flux:sidebar sticky stashable class="bg-zinc-50 dark:bg-zinc-900 border-r rtl:border-r-0 rtl:border-l border-zinc-200 dark:border-zinc-700">
        <livewire:site-sidebar />
    </flux:sidebar>
    @include('components.layouts.app.header-site')
    <flux:main class="flex overflow-hidden p-0!">
        {{ $slot ?? '' }}
        @yield('content')
    </flux:main>
    @fluxScripts
    <flux:toast />
    <!-- Real-time features can be added here when needed -->
</body>
</html>
