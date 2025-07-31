<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use App\Models\Device;
use Illuminate\Support\Facades\Auth;

class DeviceApiKeyAuth
{
    public function handle(Request $request, Closure $next)
    {
        $header = $request->header('Authorization');
        if ($header && preg_match('/Bearer\s+(.*)$/i', $header, $matches)) {
            $apiKey = $matches[1];
            $parts = explode('|', $apiKey, 2);
            if (count($parts) === 2) {
                $tokenId = $parts[0];
                $token = PersonalAccessToken::find($tokenId);
                if ($token && hash_equals($token->token, hash('sha256', $parts[1])) && $token->tokenable_type === Device::class) {
                    $device = Device::find($token->tokenable_id);
                    // Check if device is revoked
                    if ($device && $device->revoked) {
                        $device->tokens()->delete();
                        return response()->json(['message' => 'Device revoked. Please reset.'], 403);
                    }
                    // For web routes, authenticate as the user (if linked)
                    if (!str_starts_with($request->path(), 'api/') && $device && $device->user_id) {
                        $user = $device->user;
                        if ($user) {
                            Auth::setUser($user);
                            return $next($request);
                        }
                    }
                    // For API routes or unlinked devices, authenticate as the device
                    if ($device) {
                        Auth::setUser($device);
                        return $next($request);
                    }
                }
            }
        }
        return response()->json(['message' => 'Unauthenticated.'], 401);
    }
}
