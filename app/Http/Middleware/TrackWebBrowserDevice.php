<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Device;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TrackWebBrowserDevice
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only track for authenticated users on web routes
        if (Auth::check() && !$request->is('api/*')) {
            $userAgent = $request->userAgent();
            $ipAddress = $request->ip();
            $sessionId = $request->session()->getId();
            $fingerprint = $this->generateBrowserFingerprint($request);

            // Find device by session ID (most reliable for current session)
            $device = Device::where('user_id', Auth::id())
                ->where('session_id', $sessionId)
                ->where('type', 'web_browser')
                ->first();

            if (!$device) {
                // Create new web browser device
                $device = Device::create([
                    'name' => $this->generateBrowserName($userAgent),
                    'type' => 'web_browser',
                    'user_id' => Auth::id(),
                    'session_id' => $sessionId,
                    'user_agent' => $userAgent,
                    'ip_address' => $ipAddress,
                    'fingerprint' => $fingerprint,
                    'last_seen' => now(),
                ]);

                Log::info('Created new web browser device', [
                    'user_id' => Auth::id(),
                    'device_id' => $device->id,
                    'fingerprint' => substr($fingerprint, 0, 10) . '...',
                    'session_id' => $sessionId
                ]);
            } else {
                // Update last seen and IP address
                $device->update([
                    'last_seen' => now(),
                    'ip_address' => $ipAddress,
                    'fingerprint' => $fingerprint, // Update fingerprint in case it changed
                    'revoked' => false, // Reactivate the device if it was revoked
                ]);
            }
        }

        return $next($request);
    }

    private function generateBrowserFingerprint(Request $request): string
    {
        // Create a simple fingerprint using only the most stable headers
        $fingerprint = [
            'user_agent' => $request->userAgent(),
            'accept_language' => $request->header('Accept-Language'),
            'sec_ch_ua_platform' => $request->header('Sec-CH-UA-Platform'),
        ];

        // Filter out null/empty values
        $fingerprint = array_filter($fingerprint, function($value) {
            return !empty($value);
        });

        // Create a hash of the fingerprint
        return hash('sha256', json_encode($fingerprint));
    }

    private function generateBrowserName($userAgent)
    {
        // Simple browser detection
        if (str_contains($userAgent, 'Chrome')) {
            return 'Chrome Browser';
        } elseif (str_contains($userAgent, 'Firefox')) {
            return 'Firefox Browser';
        } elseif (str_contains($userAgent, 'Safari')) {
            return 'Safari Browser';
        } elseif (str_contains($userAgent, 'Edge')) {
            return 'Edge Browser';
        } else {
            return 'Web Browser';
        }
    }
}
