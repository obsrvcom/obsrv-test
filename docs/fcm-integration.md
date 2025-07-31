# Firebase Cloud Messaging (FCM) Integration

This document explains how Firebase Cloud Messaging authentication tokens are generated and used when devices are paired to users.

## Overview

When a device is paired to a user, the system automatically generates a Firebase Cloud Messaging (FCM) authentication token. This token can be used to authenticate the device with Firebase Cloud Messaging services for push notifications.

## How It Works

### 1. Device Pairing Process

When a device is paired to a user through either:
- Magic link authentication (web-based pairing)
- API login authentication

The system automatically:
1. Links the device to the user
2. Generates a unique FCM authentication token
3. Stores the token in the device record
4. Returns the token to the client

### 2. FCM Token Generation

The FCM token is generated on-demand using a deterministic algorithm:
- **Header**: Contains algorithm and token type information
- **Payload**: Contains device ID, user ID, and version information
- **Signature**: HMAC-SHA256 signature for security
- **Deterministic**: Same device-user pair always generates the same token

### 3. Token Retrieval

Devices can retrieve their FCM token through:
- The `/api/v1/device/heartbeat` endpoint (automatically returns FCM token when authenticated)
- The `/api/v1/device/fcm-token` endpoint (dedicated FCM token endpoint)
- The `/api/v1/device/refresh-fcm-token` endpoint (regenerates token)
- Direct generation using the FirebaseService

### 4. Authentication Flow

1. **Device Registration**: Device registers and gets API key
2. **Authentication Request**: Device calls `/api/v1/device/authenticate` with email
3. **Magic Link**: User receives email and clicks link to approve device
4. **Device Detection**: Device polls `/api/v1/device/heartbeat` to detect authentication and get FCM token

## API Endpoints

### Device Heartbeat
```
POST /api/v1/device/heartbeat
```

Returns device status and automatically includes FCM token when the device is authenticated.

**Response:**
```json
{
  "registered": true,
  "authenticated": true,
  "fcm_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
}
```

### Get FCM Token
```
GET /api/v1/device/fcm-token
```

Dedicated endpoint to retrieve FCM token for authenticated devices.

**Response:**
```json
{
  "fcm_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
  "device_id": 123,
  "user_id": 1
}
```

### Refresh FCM Token
```
POST /api/v1/device/refresh-fcm-token
```

Generates a new FCM token for the authenticated device.

**Response:**
```json
{
  "fcm_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
  "message": "FCM token refreshed successfully"
}
```

### Login with Device
```
POST /api/v1/auth/login
```

When a device is paired during login, the response includes the FCM token:

**Response:**
```json
{
  "token": "1|abc123...",
  "user": {...},
  "device_id": 123,
  "fcm_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
}
```

## Configuration

### Environment Variables

Add these to your `.env` file for Firebase configuration:

```env
# Firebase Configuration (optional - for future FCM server integration)
FIREBASE_PROJECT_ID=your-project-id
FIREBASE_PRIVATE_KEY_ID=your-private-key-id
FIREBASE_PRIVATE_KEY="-----BEGIN PRIVATE KEY-----\n...\n-----END PRIVATE KEY-----\n"
FIREBASE_CLIENT_EMAIL=your-service-account@your-project.iam.gserviceaccount.com
FIREBASE_CLIENT_ID=your-client-id
FIREBASE_CLIENT_X509_CERT_URL=https://www.googleapis.com/robot/v1/metadata/x509/your-service-account%40your-project.iam.gserviceaccount.com

# FCM Token Settings
FCM_TOKEN_EXPIRATION_DAYS=30
FCM_TOKEN_CACHE_PREFIX=fcm_token_
FCM_TOKEN_USE_CACHE_VALIDATION=true
```

### Configuration File

The FCM settings are managed in `config/firebase.php`:

```php
'fcm_token' => [
    'expiration_days' => env('FCM_TOKEN_EXPIRATION_DAYS', 30),
    'cache_prefix' => env('FCM_TOKEN_CACHE_PREFIX', 'fcm_token_'),
    'use_cache_validation' => env('FCM_TOKEN_USE_CACHE_VALIDATION', true),
],
```

## Usage Examples

### Client-Side Integration

```javascript
// Complete authentication flow
async function authenticateDevice(email) {
  // 1. Request authentication
  const authResponse = await fetch('/api/v1/device/authenticate', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${deviceApiKey}`,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({ email })
  });

  if (authResponse.status === 202) {
    console.log('Authentication request sent. Check your email.');
    
    // 2. Poll for authentication status
    const checkAuth = async () => {
      const heartbeatResponse = await fetch('/api/v1/device/heartbeat', {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${deviceApiKey}`,
          'Content-Type': 'application/json'
        }
      });

      const data = await heartbeatResponse.json();
      
      if (data.authenticated) {
        // 3. FCM token is automatically included in heartbeat response
        console.log('FCM Token:', data.fcm_token);
        return data.fcm_token;
      } else if (data.authentication_request_pending) {
        // Still pending, check again in 5 seconds
        setTimeout(checkAuth, 5000);
      }
    };

    checkAuth();
  }
}

// Or get FCM token directly if already authenticated
async function getFcmToken() {
  const response = await fetch('/api/v1/device/heartbeat', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${deviceApiKey}`,
      'Content-Type': 'application/json'
    }
  });

  const data = await response.json();
  return data.fcm_token; // Automatically included when authenticated
}
```

### Token Validation

The FCM token can be validated using the FirebaseService:

```php
use App\Services\FirebaseService;

$firebaseService = new FirebaseService();
$validationResult = $firebaseService->validateFcmToken($fcmToken);

if ($validationResult) {
    $deviceId = $validationResult['device_id'];
    $userId = $validationResult['user_id'];
    // Token is valid
}
```

## Security Features

1. **HMAC-SHA256 Signatures**: All tokens are signed with the application key
2. **Deterministic Generation**: Same device-user pair always generates the same token
3. **No Database Storage**: Tokens are not persisted, reducing attack surface
4. **Device-User Binding**: Tokens are bound to specific device-user pairs
5. **On-Demand Generation**: Tokens are generated only when needed

## Database Schema

**No database changes required!** FCM tokens are generated on-demand and not stored in the database. This approach:

- Eliminates database storage overhead
- Improves security (tokens not persisted)
- Simplifies the architecture
- Makes tokens deterministic (same device-user pair always generates same token)

## Testing

Run the FCM token tests:

```bash
php artisan test tests/Feature/FcmTokenTest.php
```

## Future Enhancements

1. **Server-Side FCM Integration**: Send push notifications using Firebase Admin SDK
2. **Token Rotation**: Automatic token refresh mechanisms
3. **Multi-Device Support**: Handle multiple devices per user
4. **Notification Preferences**: User-configurable notification settings 
