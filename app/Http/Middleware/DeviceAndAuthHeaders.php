<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Device;

class DeviceAndAuthHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $deviceId = $request->header('X-Device-ID');
        $authHeader = $request->header('Authorization');

        // Store in request for later use
        $request->attributes->set('device_id', $deviceId);
        $request->attributes->set('api_key', $authHeader);

        if ($deviceId) {
            $device = Device::find($deviceId);
            if (! $device) {
                return response()->json(['message' => 'Device not found'], 404);
            }
            // Only check for revoked devices on API routes, not web routes
            if ($device->revoked && $request->is('api/*')) {
                return response()->json(['message' => 'Device revoked. Please reset.'], 403);
            }
            // If user is authenticated, check device ownership
            if (auth('sanctum')->check() && $device->user_id && $device->user_id !== auth('sanctum')->id()) {
                return response()->json(['message' => 'Device does not belong to the authenticated user'], 403);
            }
            // Update last seen timestamp
            $device->update(['last_seen' => now()]);
        }

        return $next($request);
    }
}
