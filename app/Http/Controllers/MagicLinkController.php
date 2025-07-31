<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\PostLoginRedirectService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MagicLinkController extends Controller
{
    public function verify(Request $request, $token)
    {
        \Log::info('Magic link verification started', ['token' => substr($token, 0, 10) . '...']);

        // Try normal magic link first
        $magicLinkData = cache()->get("magic_link_{$token}");
        if ($magicLinkData) {
            \Log::info('Magic link cache lookup', [
                'token' => substr($token, 0, 10) . '...',
                'data_found' => !is_null($magicLinkData),
                'data' => $magicLinkData
            ]);

            if (!$magicLinkData) {
                \Log::error('Magic link invalid or expired', ['token' => substr($token, 0, 10) . '...']);
                return redirect()->route('login')->with('error', 'Invalid or expired magic link.');
            }

            // Handle backward compatibility - check if it's the old format (just email string)
            if (is_string($magicLinkData)) {
                $email = $magicLinkData;
                $remember = false; // Default to false for old format
            } else {
                // New format with email and remember preference
                $email = $magicLinkData['email'] ?? null;
                $remember = $magicLinkData['remember'] ?? false;
            }

            if (!$email) {
                \Log::error('Magic link data missing email', ['token' => substr($token, 0, 10) . '...']);
                return redirect()->route('login')->with('error', 'Invalid magic link data.');
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

            // Log the user in with remember me preference
            $isDeviceWebview = $request->header('X-Device-UUID') !== null;
            $remember = $isDeviceWebview ? true : $remember;
            Auth::login($user, $remember);
            // Regenerate session to prevent session fixation
            $request->session()->regenerate();
            if (!$remember) {
                $user->setRememberToken(null);
                $user->save();
            }
            \Log::info('User logged in via magic link', [
                'user_id' => $user->id,
                'email' => $email,
                'remember' => $remember,
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

            // Handle redirect after login using the centralized service
            $preferredRedirect = null;

            // Check for explicit redirect parameter
            if ($request->has('redirect')) {
                $preferredRedirect = $request->get('redirect');
                        } else {
                // Check if this is an invitation with redirect info
                $preferredRedirect = PostLoginRedirectService::extractInvitationRedirect($magicLinkData);
            }

            return PostLoginRedirectService::redirectAfterLogin($user, $preferredRedirect);
        }

        // If not found, try device authentication magic link
        $deviceAuthData = cache()->get("device_auth_{$token}");
        if ($deviceAuthData) {
            // Remove the token from cache (one-time use)
            cache()->forget("device_auth_{$token}");

            $user = \App\Models\User::find($deviceAuthData['user_id']);
            $device = \App\Models\Device::find($deviceAuthData['device_id']);
            $authRequest = \App\Models\DeviceAuthRequest::find($deviceAuthData['auth_request_id']);

            if (!$user || !$device || !$authRequest) {
                return view('magic-link.device-approval', ['success' => false, 'message' => 'Invalid or expired device authentication link.']);
            }

                        // Link device to user and mark as authenticated
            $device->user_id = $user->id;
            $device->save();

            // Mark request as not pending
            $authRequest->pending = false;
            $authRequest->save();

            // Generate FCM token on-demand for display
            $firebaseService = new \App\Services\FirebaseService();
            $fcmToken = $firebaseService->getFcmToken($device->id, $user->id);

            return view('magic-link.device-approval', [
                'success' => true,
                'message' => 'Device approved, please return to the app on your device.',
                'fcm_token' => $fcmToken
            ]);
        }

        // If neither found, fallback to old logic
        return redirect()->route('login')->with('error', 'Invalid or expired magic link.');
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
        // Regenerate session to prevent session fixation
        $request->session()->regenerate();

        // After forgot password, redirect using the centralized service
        return PostLoginRedirectService::redirectAfterLogin($user)->with('success', 'Your password has been removed. You can now sign in using magic links only.');
    }

    private function extractNameFromEmail($email)
    {
        $name = explode('@', $email)[0];
        return ucfirst(str_replace(['.', '_', '-'], ' ', $name));
    }
}
