<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Device;

class DeviceController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'uuid' => 'required|string|max:255|unique:devices,uuid',
        ]);

        $device = Device::create([
            'name' => $validated['name'],
            'uuid' => $validated['uuid'],
            'user_id' => null,
        ]);

        // Generate API key for the device
        $token = $device->createToken('device-api-key', [], now()->addYear());

        return response()->json([
            'api_key' => $token->plainTextToken,
        ], 201);
    }

    public function heartbeat(Request $request)
    {
        $device = Auth::user();
        if (!$device instanceof \App\Models\Device) {
            return response()->json(['registered' => false], 401);
        }

        $pendingRequest = \App\Models\DeviceAuthRequest::where('device_id', $device->id)
            ->where('pending', true)
            ->latest()
            ->first();

        $authenticated = !is_null($device->user_id);

        // Always generate FCM token if device is authenticated
        $fcmToken = null;
        if ($authenticated) {
            $firebaseService = new \App\Services\FirebaseService();
            $fcmToken = $firebaseService->getFcmToken($device->id, $device->user_id);
        }

        return response()->json([
            'registered' => true,
            'authentication_request_pending' => (bool) $pendingRequest,
            'authentication_request_email' => $pendingRequest ? $pendingRequest->email : null,
            'authenticated' => $authenticated,
            'fcm_token' => $fcmToken,
            'notifications_enabled' => $device->notifications_enabled,
        ]);
    }

    public function authenticate(Request $request)
    {
        // Rate limit device authentication by device and email
        $maxAttempts = 5;
        $decayMinutes = 5;
        $key = 'device-auth:' . ($request->user() ? $request->user()->id : $request->ip()) . ':' . $request->input('email');
        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            return response()->json(['message' => 'Too many authentication attempts. Please try again later.'], 429);
        }
        \Illuminate\Support\Facades\RateLimiter::hit($key, $decayMinutes * 60);

        $request->validate([
            'email' => 'required|email',
        ]);

        $device = Auth::user();
        if (!$device instanceof \App\Models\Device) {
            return response()->json(['message' => 'Device not authenticated'], 401);
        }

        $email = $request->input('email');
        $user = \App\Models\User::firstOrCreate(
            ['email' => $email],
            [
                'name' => ucfirst(explode('@', $email)[0]),
                'password' => null,
                'email_verified_at' => null,
            ]
        );

        // Generate a unique token
        $token = \Illuminate\Support\Str::random(64);

        // Create a pending device auth request
        $authRequest = \App\Models\DeviceAuthRequest::create([
            'device_id' => $device->id,
            'user_id' => $user->id,
            'email' => $email,
            'token' => $token,
            'pending' => true,
        ]);

        // Store the token in cache for 15 minutes (for magic link verification)
        cache()->put("device_auth_{$token}", [
            'device_id' => $device->id,
            'user_id' => $user->id,
            'email' => $email,
            'auth_request_id' => $authRequest->id,
        ], now()->addMinutes(15));

        // Send the magic link email in background
        \Mail::to($email)->queue(new \App\Mail\MagicLinkMail($token));

        return response()->json([
            'message' => 'Authentication request pending. Magic link sent to email.',
            'authentication_request_pending' => true,
            'authentication_request_email' => $email,
        ], 202);
    }

    public function getFcmToken(Request $request)
    {
        $device = Auth::user();
        if (!$device instanceof \App\Models\Device) {
            return response()->json(['message' => 'Device not authenticated'], 401);
        }

        if (!$device->user_id) {
            return response()->json(['message' => 'Device not paired with user'], 400);
        }

        $firebaseService = new \App\Services\FirebaseService();
        $fcmToken = $firebaseService->getFcmToken($device->id, $device->user_id);

        return response()->json([
            'fcm_token' => $fcmToken,
            'device_id' => $device->id,
            'user_id' => $device->user_id,
        ]);
    }

        public function refreshFcmToken(Request $request)
    {
        $device = Auth::user();
        if (!$device instanceof \App\Models\Device) {
            return response()->json(['message' => 'Device not authenticated'], 401);
        }

        if (!$device->user_id) {
            return response()->json(['message' => 'Device not paired with user'], 400);
        }

        $firebaseService = new \App\Services\FirebaseService();
        $fcmToken = $firebaseService->refreshFcmToken($device->id, $device->user_id);

        return response()->json([
            'fcm_token' => $fcmToken,
            'message' => 'FCM token refreshed successfully',
        ]);
    }
}
