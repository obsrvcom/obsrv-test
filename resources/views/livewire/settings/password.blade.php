<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Volt\Component;
use Illuminate\Support\Str;

new class extends Component {
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $remove_password_confirmation = '';
    public bool $forgotPasswordSent = false;

    /**
     * Update the password for the currently authenticated user.
     */
    public function updatePassword(): void
    {
        try {
            if ($this->hasPassword()) {
                // User has password - require current password
                $validated = $this->validate([
                    'current_password' => ['required', 'string', 'current_password'],
                    'password' => ['required', 'string', Password::defaults(), 'confirmed'],
                ]);
            } else {
                // User has no password - only require new password
                $validated = $this->validate([
                    'password' => ['required', 'string', Password::defaults(), 'confirmed'],
                ]);
            }
        } catch (ValidationException $e) {
            if ($this->hasPassword()) {
                $this->reset('current_password', 'password', 'password_confirmation');
            } else {
                $this->reset('password', 'password_confirmation');
            }

            throw $e;
        }

        Auth::user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        if ($this->hasPassword()) {
            $this->reset('current_password', 'password', 'password_confirmation');
        } else {
            $this->reset('password', 'password_confirmation');
        }

        $this->dispatch('password-updated');
    }

    /**
     * Remove the password and use magic link only.
     */
    public function removePassword(): void
    {
        $this->validate([
            'remove_password_confirmation' => ['required', 'string', 'current_password'],
        ]);

        Auth::user()->update([
            'password' => null,
        ]);

        $this->reset('remove_password_confirmation');

        $this->dispatch('password-removed');
    }

    /**
     * Send forgot password email to remove password authentication.
     */
    public function sendForgotPasswordEmail(): void
    {
        $user = Auth::user();

        // Generate a secure token
        $token = Str::random(64);

        // Store the token in cache with expiration (15 minutes)
        cache()->put("forgot_password_{$token}", $user->id, now()->addMinutes(15));

        // Send the forgot password email
        Mail::to($user->email)->send(new \App\Mail\ForgotPasswordMail($token));

        $this->forgotPasswordSent = true;
    }

    /**
     * Check if user has a password set.
     */
    public function hasPassword(): bool
    {
        return !empty(Auth::user()->password);
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Update password')" :subheading="__('Ensure your account is using a long, random password to stay secure')">
        @if(!$this->hasPassword())
            <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">
                            No password set
                        </h3>
                        <div class="mt-2 text-sm text-blue-700 dark:text-blue-300">
                            <p>
                                You're currently using magic link authentication only. You can set up a password for faster login.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if($this->hasPassword())
            <form wire:submit="updatePassword" class="mt-6 space-y-6">
                <flux:input
                    wire:model="current_password"
                    :label="__('Current password')"
                    type="password"
                    required
                    autocomplete="current-password"
                />
                <flux:input
                    wire:model="password"
                    :label="__('New password')"
                    type="password"
                    required
                    autocomplete="new-password"
                />
                <flux:input
                    wire:model="password_confirmation"
                    :label="__('Confirm Password')"
                    type="password"
                    required
                    autocomplete="new-password"
                />

                <div class="flex items-center gap-4">
                    <div class="flex items-center justify-end">
                        <flux:button variant="primary" type="submit" class="w-full">{{ __('Save') }}</flux:button>
                    </div>

                    <x-action-message class="me-3" on="password-updated">
                        {{ __('Saved.') }}
                    </x-action-message>
                </div>
            </form>

            <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Remove Password</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                    You can remove your password and use magic link authentication only. You'll need to confirm your current password.
                </p>

                <form wire:submit="removePassword" class="space-y-4">
                    <flux:input
                        wire:model="remove_password_confirmation"
                        :label="__('Confirm current password')"
                        type="password"
                        required
                        autocomplete="current-password"
                    />

                    <div class="flex items-center gap-4">
                        <flux:button
                            variant="danger"
                            type="submit"
                            wire:confirm="Are you sure you want to remove your password? You'll only be able to sign in with magic links."
                        >
                            {{ __('Remove Password') }}
                        </flux:button>

                        <x-action-message on="password-removed">
                            {{ __('Password removed.') }}
                        </x-action-message>
                    </div>
                </form>
            </div>

            <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Forgot Password?</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                    If you've forgotten your password, we can send you an email to remove password authentication and switch to magic link only.
                </p>

                @if($forgotPasswordSent)
                    <div class="p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-green-800 dark:text-green-200">
                                    Email sent
                                </h3>
                                <div class="mt-2 text-sm text-green-700 dark:text-green-300">
                                    <p>
                                        We've sent you an email with a link to remove your password. Check your inbox and click the link to switch to magic link authentication.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <flux:button
                        variant="outline"
                        wire:click="sendForgotPasswordEmail"
                        wire:confirm="This will send an email to remove your password. You'll only be able to sign in with magic links after clicking the link in the email."
                    >
                        {{ __('Send Forgot Password Email') }}
                    </flux:button>
                @endif
            </div>
        @else
            <form wire:submit="updatePassword" class="mt-6 space-y-6">
                <flux:input
                    wire:model="password"
                    :label="__('New password')"
                    type="password"
                    required
                    autocomplete="new-password"
                />
                <flux:input
                    wire:model="password_confirmation"
                    :label="__('Confirm Password')"
                    type="password"
                    required
                    autocomplete="new-password"
                />

                <div class="flex items-center gap-4">
                    <div class="flex items-center justify-end">
                        <flux:button variant="primary" type="submit" class="w-full">{{ __('Set Password') }}</flux:button>
                    </div>

                    <x-action-message class="me-3" on="password-updated">
                        {{ __('Password set.') }}
                    </x-action-message>
                </div>
            </form>
        @endif
    </x-settings.layout>
</section>
