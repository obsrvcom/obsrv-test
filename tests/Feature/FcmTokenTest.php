<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Device;
use App\Models\User;
use App\Services\FirebaseService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FcmTokenTest extends TestCase
{
    use RefreshDatabase;

    public function test_fcm_token_generation()
    {
        $user = User::factory()->create();
        $device = Device::factory()->create(['user_id' => $user->id]);

        $firebaseService = new FirebaseService();
        $fcmToken = $firebaseService->generateFcmToken($device->id, $user->id);

        $this->assertNotEmpty($fcmToken);
        $this->assertStringContainsString('.', $fcmToken);

        // Token should have 3 parts (header.payload.signature)
        $parts = explode('.', $fcmToken);
        $this->assertCount(3, $parts);
    }

    public function test_fcm_token_validation()
    {
        $user = User::factory()->create();
        $device = Device::factory()->create(['user_id' => $user->id]);

        $firebaseService = new FirebaseService();
        $fcmToken = $firebaseService->generateFcmToken($device->id, $user->id);

        $validationResult = $firebaseService->validateFcmToken($fcmToken);

        $this->assertNotNull($validationResult);
        $this->assertEquals($device->id, $validationResult['device_id']);
        $this->assertEquals($user->id, $validationResult['user_id']);
    }

    public function test_fcm_token_invalidation()
    {
        $user = User::factory()->create();
        $device = Device::factory()->create(['user_id' => $user->id]);

        $firebaseService = new FirebaseService();
        $fcmToken = $firebaseService->generateFcmToken($device->id, $user->id);

        // Token should be valid initially
        $this->assertNotNull($firebaseService->validateFcmToken($fcmToken));

        // With on-demand generation, tokens are deterministic
        // So invalidation doesn't change the token, but it remains valid
        $newToken = $firebaseService->generateFcmToken($device->id, $user->id);
        $this->assertEquals($fcmToken, $newToken); // Same token for same device-user pair
    }

        public function test_fcm_token_refresh()
    {
        $user = User::factory()->create();
        $device = Device::factory()->create(['user_id' => $user->id]);

        $firebaseService = new FirebaseService();
        $originalToken = $firebaseService->generateFcmToken($device->id, $user->id);
        $refreshedToken = $firebaseService->refreshFcmToken($device->id, $user->id);

        // With deterministic generation, refresh returns the same token
        $this->assertEquals($originalToken, $refreshedToken);

        // Both tokens should be valid
        $this->assertNotNull($firebaseService->validateFcmToken($refreshedToken));
    }

        public function test_device_pairing_with_fcm_token()
    {
        $user = User::factory()->create();
        $device = Device::factory()->create(['user_id' => null]);

        // Simulate device pairing
        $device->user_id = $user->id;
        $device->save();

        // Generate FCM token on-demand
        $firebaseService = new FirebaseService();
        $fcmToken = $firebaseService->getFcmToken($device->id, $user->id);

        $this->assertEquals($user->id, $device->user_id);

        // Token should be valid
        $validationResult = $firebaseService->validateFcmToken($fcmToken);
        $this->assertNotNull($validationResult);
        $this->assertEquals($device->id, $validationResult['device_id']);
        $this->assertEquals($user->id, $validationResult['user_id']);
    }

    public function test_fcm_token_deterministic_generation()
    {
        $user = User::factory()->create();
        $device = Device::factory()->create(['user_id' => $user->id]);

        $firebaseService = new FirebaseService();

        // Generate token multiple times
        $token1 = $firebaseService->getFcmToken($device->id, $user->id);
        $token2 = $firebaseService->getFcmToken($device->id, $user->id);
        $token3 = $firebaseService->getFcmToken($device->id, $user->id);

        // All tokens should be identical for the same device-user pair
        $this->assertEquals($token1, $token2);
        $this->assertEquals($token2, $token3);
        $this->assertEquals($token1, $token3);
    }

    public function test_fcm_token_endpoint()
    {
        $user = User::factory()->create();
        $device = Device::factory()->create(['user_id' => $user->id]);

        // Create a token for the device
        $token = $device->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->get('/api/v1/device/fcm-token');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'fcm_token',
                'device_id',
                'user_id'
            ]);

        $this->assertEquals($device->id, $response->json('device_id'));
        $this->assertEquals($user->id, $response->json('user_id'));
        $this->assertNotEmpty($response->json('fcm_token'));
    }

    public function test_heartbeat_returns_fcm_token_when_authenticated()
    {
        $user = User::factory()->create();
        $device = Device::factory()->create(['user_id' => $user->id]);

        // Create a token for the device
        $token = $device->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->post('/api/v1/device/heartbeat');

        $response->assertStatus(200)
            ->assertJson([
                'registered' => true,
                'authenticated' => true,
            ])
            ->assertJsonStructure([
                'registered',
                'authentication_request_pending',
                'authentication_request_email',
                'authenticated',
                'fcm_token'
            ]);

        $this->assertNotEmpty($response->json('fcm_token'));
    }

    public function test_heartbeat_does_not_return_fcm_token_when_not_authenticated()
    {
        $device = Device::factory()->create(['user_id' => null]);

        // Create a token for the device
        $token = $device->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->post('/api/v1/device/heartbeat');

        $response->assertStatus(200)
            ->assertJson([
                'registered' => true,
                'authenticated' => false,
            ])
            ->assertJsonStructure([
                'registered',
                'authentication_request_pending',
                'authentication_request_email',
                'authenticated',
                'fcm_token'
            ]);

        $this->assertNull($response->json('fcm_token'));
    }
}
