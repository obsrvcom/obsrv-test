<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class FirebaseService
{
    /**
     * Generate a Firebase Cloud Messaging authentication token for a device
     * This generates a deterministic token based on device and user IDs
     *
     * @param int $deviceId
     * @param int $userId
     * @return string
     */
    public function generateFcmToken(int $deviceId, int $userId): string
    {
        // Generate a deterministic token based on device and user IDs
        $tokenData = [
            'device_id' => $deviceId,
            'user_id' => $userId,
            'version' => config('firebase.fcm_token.version', '1.0'),
        ];

        // Create a JWT-like token structure
        $header = base64_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $payload = base64_encode(json_encode($tokenData));
        $signature = hash_hmac('sha256', $header . '.' . $payload, config('app.key'));

        $fcmToken = $header . '.' . $payload . '.' . $signature;

        if (config('firebase.fcm_token.log_generation', true)) {
            Log::info('FCM token generated on-demand', [
                'device_id' => $deviceId,
                'user_id' => $userId,
                'token_length' => strlen($fcmToken)
            ]);
        }

        return $fcmToken;
    }

    /**
     * Validate a Firebase Cloud Messaging token
     *
     * @param string $token
     * @return array|null
     */
    public function validateFcmToken(string $token): ?array
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            return null;
        }

        [$header, $payload, $signature] = $parts;

        // Verify signature
        $expectedSignature = hash_hmac('sha256', $header . '.' . $payload, config('app.key'));

        if (!hash_equals($signature, $expectedSignature)) {
            return null;
        }

        // Decode payload
        $payloadData = json_decode(base64_decode($payload), true);

        if (!$payloadData || !isset($payloadData['device_id'], $payloadData['user_id'])) {
            return null;
        }

        return $payloadData;
    }

    /**
     * Generate a new FCM token (for compatibility with refresh endpoints)
     * Since tokens are deterministic, this just regenerates the same token
     *
     * @param int $deviceId
     * @param int $userId
     * @return string
     */
    public function refreshFcmToken(int $deviceId, int $userId): string
    {
        // For on-demand generation, refresh just regenerates the same token
        // This maintains API compatibility while using deterministic generation
        return $this->generateFcmToken($deviceId, $userId);
    }

    /**
     * Get FCM token for a device-user pair
     * This is the main method for retrieving tokens on-demand
     *
     * @param int $deviceId
     * @param int $userId
     * @return string
     */
    public function getFcmToken(int $deviceId, int $userId): string
    {
        return $this->generateFcmToken($deviceId, $userId);
    }
}
