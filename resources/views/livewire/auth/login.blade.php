<?php

use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;
use App\Models\User;

new #[Layout('components.layouts.auth')] class extends Component {
    #[Validate('required|string|email')]
    public string $email = '';

    #[Validate('required|string')]
    public string $password = '';

    public bool $remember = false;
    public bool $linkSent = false;
    public bool $showPasswordForm = false;
    public ?User $existingUser = null;

    /**
     * Check if user exists and has password set.
     */
    public function checkUser(): void
    {
        $this->validate(['email' => 'required|string|email']);

        $this->existingUser = User::where('email', $this->email)->first();

        if ($this->existingUser && $this->existingUser->password && $this->existingUser->password !== '') {
            // User exists and has password - show password form
            $this->showPasswordForm = true;
        } else {
            // User doesn't exist or has no password - send magic link
            $this->sendMagicLink();
        }
    }

    /**
     * Handle password authentication.
     */
    public function loginWithPassword(): void
    {
        $this->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $this->ensureIsNotRateLimited();

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'password' => __('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
        Session::regenerate();

        // Determine redirect based on current domain
        $host = request()->getHost();
        $subdomain = $this->extractSubdomain($host);

        if (!$subdomain || $subdomain === 'www') {
            // On main domain - redirect to company selection
            $this->redirect(route('company.select', absolute: false), navigate: true);
        } else {
            // On subdomain - redirect to dashboard
            $this->redirect(route('dashboard', absolute: false), navigate: true);
        }
    }

    /**
     * Send magic link for authentication.
     */
    public function sendMagicLink(): void
    {
        $this->validate(['email' => 'required|string|email']);

        $this->ensureIsNotRateLimited();

        // Generate a secure token
        $token = Str::random(64);

        // Store the token in cache with expiration (15 minutes)
        cache()->put("magic_link_{$token}", $this->email, now()->addMinutes(15));

        // Send the magic link email
        \Illuminate\Support\Facades\Mail::to($this->email)->send(new \App\Mail\MagicLinkMail($token));

        $this->linkSent = true;
        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Go back to email input.
     */
    public function backToEmail(): void
    {
        $this->showPasswordForm = false;
        $this->existingUser = null;
        $this->password = '';
    }

    /**
     * Ensure the authentication request is not rate limited.
     */
    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => __('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the authentication rate limiting throttle key.
     */
    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->email).'|'.request()->ip());
    }

    /**
     * Extract subdomain from host.
     */
    private function extractSubdomain($host)
    {
        $parts = explode('.', $host);

        if (count($parts) <= 2) {
            return null; // No subdomain
        }

        return $parts[0];
    }
}; ?>

<div class="flex flex-col gap-6">
    <x-auth-header :title="__('Sign in to your account')" :description="__('Enter your email address to get started')" />

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')" />

    @if($linkSent)
        <div class="text-center">
            <div class="mx-auto h-12 w-12 text-green-500 mb-4">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Check your email</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                We've sent a magic link to <strong>{{ $email }}</strong>
            </p>
            <p class="text-xs text-gray-500 dark:text-gray-500">
                Click the link in your email to sign in. The link will expire in 15 minutes.
            </p>
            @if(!$existingUser || !$existingUser->password)
                <p class="text-xs text-blue-600 dark:text-blue-400 mt-2">
                    💡 You can set up a password in your settings for faster login next time.
                </p>
            @endif
            <flux:button
                variant="outline"
                wire:click="$set('linkSent', false)"
                class="mt-4"
            >
                Send another link
            </flux:button>
        </div>
    @elseif($showPasswordForm)
        <div>
            <div class="mb-4">
                <flux:button
                    variant="ghost"
                    size="sm"
                    wire:click="backToEmail"
                    class="mb-4"
                >
                    ← Back to email
                </flux:button>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Welcome back! Choose how you'd like to sign in to <strong>{{ $email }}</strong>
                </p>
            </div>

            <div class="space-y-4">
                <!-- Password Login -->
                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                    <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-3">Sign in with password</h3>
                    <form wire:submit="loginWithPassword" class="space-y-4">
                        <flux:input
                            wire:model="password"
                            :label="__('Password')"
                            type="password"
                            required
                            autofocus
                            autocomplete="current-password"
                            :placeholder="__('Enter your password')"
                            viewable
                        />

                        <flux:checkbox wire:model="remember" :label="__('Remember me')" />

                        <flux:button variant="primary" type="submit" class="w-full">
                            {{ __('Sign in with Password') }}
                        </flux:button>
                    </form>
                </div>

                <!-- Magic Link Option -->
                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                    <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-3">Sign in with magic link</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                        We'll send you a secure link to sign in without a password.
                    </p>
                    <flux:button
                        variant="outline"
                        wire:click="sendMagicLink"
                        class="w-full"
                    >
                        {{ __('Send Magic Link') }}
                    </flux:button>
                </div>
            </div>
        </div>
    @else
        <form wire:submit="checkUser" class="flex flex-col gap-6">
            <!-- Email Address -->
            <flux:input
                wire:model="email"
                :label="__('Email address')"
                type="email"
                required
                autofocus
                autocomplete="email"
                placeholder="email@example.com"
            />

            <div class="flex items-center justify-end space-x-2">
                <flux:button variant="primary" type="submit" class="w-full">{{ __('Continue') }}</flux:button>
            </div>
        </form>
    @endif
</div>
