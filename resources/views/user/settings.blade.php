@extends('components.layouts.user')

@section('content')
<div class="max-w-4xl mx-auto py-10">
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-neutral-200 dark:border-neutral-700 p-8">
        <h1 class="text-2xl font-bold mb-4">User Settings</h1>
        <p class="text-gray-600 dark:text-gray-400 mb-6">Manage your personal account settings here.</p>
        <div class="flex flex-col md:flex-row gap-8">
            <nav class="md:w-1/4 mb-6 md:mb-0">
                <ul class="space-y-2">
                    <li><a href="{{ route('settings.profile') }}" class="hover:underline">Profile</a></li>
                    <li><a href="{{ route('settings.password') }}" class="hover:underline">Password</a></li>
                    <li><a href="{{ route('settings.appearance') }}" class="hover:underline">Appearance</a></li>
                    <li><a href="{{ route('settings.devices') }}" class="hover:underline">Devices & Sessions</a></li>
                </ul>
            </nav>
            <div class="flex-1">
                @if(request()->routeIs('settings.profile'))
                    @include('livewire.user.settings.profile')
                @elseif(request()->routeIs('settings.password'))
                    @include('livewire.user.settings.password')
                @elseif(request()->routeIs('settings.appearance'))
                    @include('livewire.user.settings.appearance')
                @elseif(request()->routeIs('settings.devices'))
                    @include('livewire.user.settings.devices')
                @else
                    <p>Select a settings section from the menu.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
