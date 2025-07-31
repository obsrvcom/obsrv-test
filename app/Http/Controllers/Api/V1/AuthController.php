<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Device;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // Rate limit login attempts by IP and email
        $maxAttempts = 5;
        $decayMinutes = 1;
        $key = 'login:' . $request->ip() . ':' . $request->input('email');
        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            return response()->json(['message' => 'Too many login attempts. Please try again later.'], 429);
        }
        \Illuminate\Support\Facades\RateLimiter::hit($key, $decayMinutes * 60);

        $validated = $request->validate([
            'device_id' => 'required|exists:devices,id',
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $validated['email'])->first();
        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

                // Associate device with user if not already
        $device = Device::find($validated['device_id']);
        if ($device->user_id !== $user->id) {
            $device->user_id = $user->id;
            $device->save();
        }

        // Also log in the user with remember me (for session-based webviews)
        if ($request->header('X-Device-UUID')) {
            Auth::login($user, true);

            // For webview requests, we might want to provide a redirect URL
            $redirectUrl = \App\Services\PostLoginRedirectService::getPostLoginRedirect($user);
        }

        // Token name includes device id for traceability
        $token = $user->createToken('device-' . $device->id, [], now()->addYear());

        // Generate FCM token on-demand
        $firebaseService = new \App\Services\FirebaseService();
        $fcmToken = $firebaseService->getFcmToken($device->id, $user->id);

        $responseData = [
            'token' => $token->plainTextToken,
            'user' => $user,
            'device_id' => $device->id,
            'fcm_token' => $fcmToken,
        ];

        // Add redirect URL for webview requests
        if ($request->header('X-Device-UUID') && isset($redirectUrl)) {
            $responseData['redirect_url'] = $redirectUrl;
        }

        return response()->json($responseData);
    }
}
