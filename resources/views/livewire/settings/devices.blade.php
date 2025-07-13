<?php

use App\Models\Device;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Volt\Component;

new class extends Component {
    public bool $showRevoked = false;
    public string $activeTab = 'devices'; // 'devices' or 'sessions'

    public function getDevicesProperty()
    {
        // Only show mobile devices (non-web browsers)
        $query = Auth::user()->devices()
            ->where('type', '!=', 'web_browser')
            ->orderBy('last_seen', 'desc');

        if (!$this->showRevoked) {
            $query->where('revoked', false);
        }

        return $query->get();
    }

    public function getSessionsProperty()
    {
        // Only show web browser sessions
        $query = Auth::user()->devices()
            ->where('type', 'web_browser')
            ->orderBy('last_seen', 'desc');

        if (!$this->showRevoked) {
            $query->where('revoked', false);
        }

        return $query->get();
    }

    public function isCurrentDevice($device)
    {
        if ($device->isWebBrowser()) {
            // Use session ID for more reliable current device identification
            return $device->session_id === session()->getId();
        }
        return false; // For mobile devices, we can't determine current device from web
    }

    private function getCurrentDeviceFingerprint()
    {
        // Use the same fingerprint generation logic as the middleware
        $fingerprint = [
            'user_agent' => request()->userAgent(),
            'accept_language' => request()->header('Accept-Language'),
            'sec_ch_ua_platform' => request()->header('Sec-CH-UA-Platform'),
            'sec_ch_ua_mobile' => request()->header('Sec-CH-UA-Mobile'),
        ];

        // Filter out null/empty values to make fingerprint more stable
        $fingerprint = array_filter($fingerprint, function($value) {
            return !empty($value);
        });

        return hash('sha256', json_encode($fingerprint));
    }

    public function revokeDevice($deviceId)
    {
        $device = Auth::user()->devices()->findOrFail($deviceId);
        $device->update(['revoked' => true]);

        // If this is a web browser device and it's the current session, log out
        if ($device->isWebBrowser() && $device->session_id === session()->getId()) {
            Auth::logout();
            Session::invalidate();
            Session::regenerateToken();

            $this->redirect(route('login'));
            return;
        }

        $this->dispatch('device-revoked');
    }

    public function revokeAllDevices()
    {
        $devices = Auth::user()->devices()
            ->where('revoked', false)
            ->where('type', '!=', 'web_browser')
            ->get();

        foreach ($devices as $device) {
            $device->update(['revoked' => true]);
        }

        $this->dispatch('devices-revoked');
    }

    public function revokeAllSessions()
    {
        $sessions = Auth::user()->devices()
            ->where('revoked', false)
            ->where('type', 'web_browser')
            ->get();

        foreach ($sessions as $session) {
            $session->update(['revoked' => true]);
        }

        // Log out current session since all sessions are revoked
        Auth::logout();
        Session::invalidate();
        Session::regenerateToken();

        $this->redirect(route('login'));
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Devices & Sessions')" :subheading="__('Manage your connected devices and web sessions')">
        <!-- Tab Navigation -->
        <div class="mb-6">
            <div class="border-b border-gray-200 dark:border-gray-700">
                <nav class="-mb-px flex space-x-8">
                    <button
                        wire:click="$set('activeTab', 'devices')"
                        class="py-2 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'devices' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}"
                    >
                        Devices
                    </button>
                    <button
                        wire:click="$set('activeTab', 'sessions')"
                        class="py-2 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'sessions' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}"
                    >
                        Web Sessions
                    </button>
                </nav>
            </div>
        </div>

        <div class="space-y-6">
            @if($activeTab === 'devices')
                @if($this->devices->count() > 0)
                    <div class="flex justify-between items-center p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                        <div>
                            <h3 class="text-sm font-medium text-red-800 dark:text-red-200">
                                Revoke All Devices
                            </h3>
                            <p class="text-sm text-red-700 dark:text-red-300 mt-1">
                                This will revoke access to all your mobile devices.
                            </p>
                        </div>
                        <flux:button
                            variant="danger"
                            size="sm"
                            wire:click="revokeAllDevices"
                            wire:confirm="Are you sure you want to revoke ALL mobile devices?"
                        >
                            Revoke All Devices
                        </flux:button>
                    </div>
                @endif

                @if($this->devices->count() > 0)
                    <div class="space-y-4">
                        @foreach($this->devices as $device)
                            <div class="flex items-center justify-between p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-3">
                                        <div class="flex-shrink-0">
                                            <div class="w-10 h-10 bg-gray-100 dark:bg-gray-800 rounded-lg flex items-center justify-center">
                                                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center space-x-2">
                                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                                    {{ $device->name }}
                                                </p>
                                            </div>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ $device->type ?? 'Unknown type' }}
                                            </p>
                                            @if($device->last_seen)
                                                <p class="text-xs text-gray-400 dark:text-gray-500">
                                                    Last seen: {{ $device->last_seen->diffForHumans() }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    @if($device->revoked)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                            Revoked
                                        </span>
                                    @else
                                        <flux:button
                                            variant="danger"
                                            size="sm"
                                            wire:click="revokeDevice({{ $device->id }})"
                                            wire:confirm="Are you sure you want to revoke this device?"
                                        >
                                            Revoke
                                        </flux:button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12">
                        <div class="mx-auto h-12 w-12 text-gray-400">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No mobile devices</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            You haven't connected any mobile devices yet.
                        </p>
                    </div>
                @endif
            @elseif($activeTab === 'sessions')
                @if($this->sessions->count() > 0)
                    <div class="flex justify-between items-center p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                        <div>
                            <h3 class="text-sm font-medium text-red-800 dark:text-red-200">
                                Revoke All Sessions
                            </h3>
                            <p class="text-sm text-red-700 dark:text-red-300 mt-1">
                                This will revoke access to all your web browser sessions and log you out immediately.
                            </p>
                        </div>
                        <flux:button
                            variant="danger"
                            size="sm"
                            wire:click="revokeAllSessions"
                            wire:confirm="Are you sure you want to revoke ALL web sessions? This will log you out immediately."
                        >
                            Revoke All Sessions
                        </flux:button>
                    </div>
                @endif

                @if($this->sessions->count() > 0)
                    <div class="space-y-4">
                        @foreach($this->sessions as $session)
                            <div class="flex items-center justify-between p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-3">
                                        <div class="flex-shrink-0">
                                            <div class="w-10 h-10 bg-gray-100 dark:bg-gray-800 rounded-lg flex items-center justify-center">
                                                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center space-x-2">
                                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                                    {{ $session->name }}
                                                </p>
                                                @if($this->isCurrentDevice($session))
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                        This Session
                                                    </span>
                                                @endif
                                            </div>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                                    Web Browser
                                                </span>
                                                @if($session->ip_address)
                                                    <span class="text-xs text-gray-400 dark:text-gray-500 ml-2">
                                                        IP: {{ $session->ip_address }}
                                                    </span>
                                                @endif
                                            </p>
                                            @if($session->last_seen)
                                                <p class="text-xs text-gray-400 dark:text-gray-500">
                                                    Last seen: {{ $session->last_seen->diffForHumans() }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    @if($session->revoked)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                            Revoked
                                        </span>
                                    @else
                                        <flux:button
                                            variant="danger"
                                            size="sm"
                                            wire:click="revokeDevice({{ $session->id }})"
                                            wire:confirm="Are you sure you want to revoke this session? This will log you out immediately."
                                        >
                                            Revoke
                                        </flux:button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12">
                        <div class="mx-auto h-12 w-12 text-gray-400">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No web sessions</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            You haven't signed in from any web browsers yet.
                        </p>
                    </div>
                @endif
            @endif

            <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                <flux:button
                    variant="outline"
                    size="sm"
                    wire:click="$toggle('showRevoked')"
                >
                    {{ $showRevoked ? 'Hide' : 'Show' }} Revoked {{ $activeTab === 'devices' ? 'Devices' : 'Sessions' }}
                </flux:button>
            </div>
        </div>

        <x-action-message class="mt-4" on="device-revoked">
            {{ __('Device has been revoked.') }}
        </x-action-message>

        <x-action-message class="mt-4" on="devices-revoked">
            {{ __('All devices have been revoked.') }}
        </x-action-message>
    </x-settings.layout>
</section>
