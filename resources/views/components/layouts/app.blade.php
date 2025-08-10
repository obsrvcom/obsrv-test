<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    @include('partials.head')
</head>
<body class="min-h-screen dark:bg-zinc-800">
    <div class="flex flex-col h-screen">
        {{ $slot ?? '' }}
        @yield('content')
    </div>
    @fluxScripts
</body>
</html>
