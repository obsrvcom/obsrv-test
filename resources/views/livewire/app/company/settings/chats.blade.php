<?php

use function Livewire\Volt\{state, mount, layout};

layout('components.layouts.company');

?>

<div class="flex h-full w-full flex-1 flex-col">
    <!-- Navigation -->
    <div class="bg-gray-50 dark:bg-gray-800 border-b border-neutral-200 dark:border-neutral-700 px-4 flex items-center gap-4">
        <flux:badge color="zinc">Company Settings</flux:badge>

        <flux:navbar>
            <flux:navbar.item
                :href="route('company.settings.profile', ['company' => request()->route('company')])"
                icon="user"
                wire:navigate
            >
                Profile
            </flux:navbar.item>
            <flux:navbar.item
                :href="route('company.settings.chats', ['company' => request()->route('company')])"
                :current="true"
                icon="chat-bubble-left-right"
                wire:navigate
            >
                Chats
            </flux:navbar.item>
        </flux:navbar>
    </div>

    <div class="p-4 flex flex-col gap-4">
        <!-- Header Section -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-neutral-200 dark:border-neutral-700 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        Chat Settings
                    </h1>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">
                        Configure chat functionality and preferences.
                    </p>
                </div>
            </div>
        </div>

        <!-- Chat Settings Content -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-neutral-200 dark:border-neutral-700 flex-1 p-6">
            <div class="text-center py-16">
                <div class="mx-auto h-16 w-16 rounded-full bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center">
                    <flux:icon name="chat-bubble-left-right" class="h-8 w-8 text-neutral-400" />
                </div>
                <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">{{ __('Chat Settings Coming Soon') }}</h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 max-w-sm mx-auto">
                    {{ __('Chat configuration and settings functionality will be implemented here.') }}
                </p>
            </div>
        </div>
    </div>
</div>
