<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use App\Models\Device;
use Illuminate\Support\Facades\Auth;

class DeviceApiKeyWebAuth
{
    public function handle(Request $request, Closure $next)
    {
        // Only run for web (not API) routes
        if (!str_starts_with($request->path(), 'api/')) {
            $header = $request->header('Authorization');
            if ($header && preg_match('/Bearer\s+(.*)$/i', $header, $matches)) {
                $apiKey = $matches[1];
                $parts = explode('|', $apiKey, 2);
                if (count($parts) === 2) {
                    $tokenId = $parts[0];
                    $token = PersonalAccessToken::find($tokenId);
                    if ($token && hash_equals($token->token, hash('sha256', $parts[1])) && $token->tokenable_type === Device::class) {
                        $device = Device::find($token->tokenable_id);
                        if ($device && $device->user_id) {
                            $user = $device->user;
                            if ($user && (!Auth::check() || Auth::id() !== $user->id)) {
                                Auth::login($user);
                                // Store device and user in session for traceability
                                session([
                                    'authenticated_by_device_id' => $device->id,
                                    'authenticated_by_user_id' => $user->id,
                                    'authenticated_by_device_api_key' => true,
                                ]);
                            }
                        }
                    }
                }
            }
        }
        return $next($request);
    }
}
