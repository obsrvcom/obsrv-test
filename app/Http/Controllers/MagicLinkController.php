<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MagicLinkController extends Controller
{
    public function verify(Request $request, $token)
    {
        \Log::info('Magic link verification started', ['token' => substr($token, 0, 10) . '...']);

        // Get the email from cache using the token
        $email = cache()->get("magic_link_{$token}");
        \Log::info('Magic link cache lookup', [
            'token' => substr($token, 0, 10) . '...',
            'email_found' => !is_null($email),
            'email' => $email
        ]);

        if (!$email) {
            \Log::error('Magic link invalid or expired', ['token' => substr($token, 0, 10) . '...']);
            return redirect()->route('login')->with('error', 'Invalid or expired magic link.');
        }

        // Remove the token from cache (one-time use)
        cache()->forget("magic_link_{$token}");

        // Find or create the user
        $user = User::where('email', $email)->first();
        \Log::info('User lookup for magic link', [
            'email' => $email,
            'user_found' => !is_null($user),
            'user_id' => $user ? $user->id : null
        ]);

        if (!$user) {
            // Create new user automatically
            $user = User::create([
                'name' => $this->extractNameFromEmail($email),
                'email' => $email,
                'password' => null, // No password - magic link only
                'email_verified_at' => now(), // Auto-verify since they clicked the email link
            ]);
            \Log::info('New user created via magic link', ['user_id' => $user->id, 'email' => $email]);
        }

        // Log the user in
        Auth::login($user);
        \Log::info('User logged in via magic link', [
            'user_id' => $user->id,
            'email' => $email,
            'auth_check' => Auth::check(),
            'session_id' => session()->getId(),
            'session_domain' => config('session.domain'),
            'session_secure' => config('session.secure'),
            'session_same_site' => config('session.same_site')
        ]);

        \Log::info('Session info after login', [
            'session_id' => session()->getId(),
            'auth_check' => Auth::check(),
            'session_cookie_name' => config('session.cookie'),
            'session_domain' => config('session.domain'),
            'session_path' => config('session.path')
        ]);

        // Determine redirect based on current domain
        $host = request()->getHost();
        $subdomain = $this->extractSubdomain($host);

                        if (!$subdomain || $subdomain === 'www') {
            // On main domain - redirect to company selection
            \Log::info('Redirecting to company selection from main domain');
            return redirect()->route('company.select');
        } else {
            // On subdomain - redirect to dashboard
            \Log::info('Redirecting to dashboard from subdomain', ['subdomain' => $subdomain]);
            return redirect()->route('dashboard');
        }
    }

    public function verifyForgotPassword(Request $request, $token)
    {
        // Get the user ID from cache using the token
        $userId = cache()->get("forgot_password_{$token}");

        if (!$userId) {
            return redirect()->route('login')->with('error', 'Invalid or expired forgot password link.');
        }

        // Remove the token from cache (one-time use)
        cache()->forget("forgot_password_{$token}");

        // Find the user
        $user = User::find($userId);

        if (!$user) {
            return redirect()->route('login')->with('error', 'User not found.');
        }

        // Remove the password
        $user->update(['password' => null]);

        // Log the user in
        Auth::login($user);

        // Determine redirect based on current domain
        $host = request()->getHost();
        $subdomain = $this->extractSubdomain($host);

        if (!$subdomain || $subdomain === 'www') {
            // On main domain - redirect to company selection
            return redirect(route('company.select'))->with('success', 'Your password has been removed. You can now sign in using magic links only.');
        } else {
            // On subdomain - redirect to dashboard
            return redirect(route('dashboard'))->with('success', 'Your password has been removed. You can now sign in using magic links only.');
        }
    }

    private function extractNameFromEmail($email)
    {
        $name = explode('@', $email)[0];
        return ucfirst(str_replace(['.', '_', '-'], ' ', $name));
    }

    private function extractSubdomain($host)
    {
        $parts = explode('.', $host);

        if (count($parts) <= 2) {
            return null; // No subdomain
        }

        return $parts[0];
    }
}
