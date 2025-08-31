<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    @include('partials.head')
</head>
<body class="min-h-screen bg-zinc-50 dark:bg-zinc-800">
    <flux:sidebar sticky stashable class="bg-zinc-50 max-lg:border-e border-zinc-200 dark:border-zinc-700 ">
        <livewire:company-sidebar />
    </flux:sidebar>

    <flux:main class="!p-0 flex flex-col space-y-2 overflow-hidden mt-2 bg-white border rounded-tl-lg">
        <div class="py-4 px-6 border-b border-b-zinc-200">
            <x-breadcrumbs />
        </div>
        <div class="p-4">
        {{ $slot ?? '' }}
        @yield('content')
        </div>
    </flux:main>
    @fluxScripts
    <flux:toast />
</body>
</html>
