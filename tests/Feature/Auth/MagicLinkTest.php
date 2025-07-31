<?php

use App\Models\User;
use App\Mail\MagicLinkMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    Mail::fake();
});

test('magic link can be sent with remember me preference', function () {
    $user = User::factory()->create(['password' => null]);

    // Simulate the Livewire component sending magic link with remember me
    $token = \Illuminate\Support\Str::random(64);
    $magicLinkData = [
        'email' => $user->email,
        'remember' => true,
    ];
    Cache::put("magic_link_{$token}", $magicLinkData, now()->addMinutes(15));

    // Verify the magic link
    $response = $this->get("/magic-link/{$token}");

    $response->assertRedirect();
    $this->assertAuthenticated();

    // Check that the user is remembered (Laravel sets remember_token when remember=true)
    $this->assertNotNull(Auth::user()->remember_token);
});

test('magic link without remember me does not set remember token', function () {
    $user = User::factory()->create(['password' => null]);

    // Simulate the Livewire component sending magic link without remember me
    $token = \Illuminate\Support\Str::random(64);
    $magicLinkData = [
        'email' => $user->email,
        'remember' => false,
    ];
    Cache::put("magic_link_{$token}", $magicLinkData, now()->addMinutes(15));

    // Verify the magic link
    $response = $this->get("/magic-link/{$token}");

    $response->assertRedirect();
    $this->assertAuthenticated();

    // Check that the user is not remembered
    $this->assertNull(Auth::user()->remember_token);
});

test('magic link creates new user if email does not exist', function () {
    $email = 'newuser@example.com';

    // Simulate the Livewire component sending magic link
    $token = \Illuminate\Support\Str::random(64);
    $magicLinkData = [
        'email' => $email,
        'remember' => true,
    ];
    Cache::put("magic_link_{$token}", $magicLinkData, now()->addMinutes(15));

    // Verify the magic link
    $response = $this->get("/magic-link/{$token}");

    $response->assertRedirect();
    $this->assertAuthenticated();

    $user = Auth::user();
    $this->assertEquals($email, $user->email);
    $this->assertTrue((bool) $user->email_verified_at);
    $this->assertNull($user->password);
});

test('magic link with backward compatibility works', function () {
    $user = User::factory()->create(['password' => null]);

    // Simulate old format magic link (just email string)
    $token = \Illuminate\Support\Str::random(64);
    Cache::put("magic_link_{$token}", $user->email, now()->addMinutes(15));

    // Verify the magic link
    $response = $this->get("/magic-link/{$token}");

    $response->assertRedirect();
    $this->assertAuthenticated();

    // Should default to not remembered for old format
    $this->assertNull(Auth::user()->remember_token);
});

test('invalid magic link token returns error', function () {
    $response = $this->get('/magic-link/invalid-token');

    $response->assertRedirect('/login');
    $response->assertSessionHas('error', 'Invalid or expired magic link.');
    $this->assertGuest();
});

test('expired magic link token returns error', function () {
    $user = User::factory()->create(['password' => null]);

    // Create an expired token
    $token = \Illuminate\Support\Str::random(64);
    $magicLinkData = [
        'email' => $user->email,
        'remember' => false,
    ];
    Cache::put("magic_link_{$token}", $magicLinkData, now()->subMinute());

    $response = $this->get("/magic-link/{$token}");

    $response->assertRedirect('/login');
    $response->assertSessionHas('error', 'Invalid or expired magic link.');
    $this->assertGuest();
});
