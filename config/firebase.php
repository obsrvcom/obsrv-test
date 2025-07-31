<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Firebase Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for Firebase Cloud Messaging (FCM)
    | integration. You can configure your Firebase project settings here.
    |
    */

    'project_id' => env('FIREBASE_PROJECT_ID'),

    'private_key_id' => env('FIREBASE_PRIVATE_KEY_ID'),

    'private_key' => env('FIREBASE_PRIVATE_KEY'),

    'client_email' => env('FIREBASE_CLIENT_EMAIL'),

    'client_id' => env('FIREBASE_CLIENT_ID'),

    'auth_uri' => env('FIREBASE_AUTH_URI', 'https://accounts.google.com/o/oauth2/auth'),

    'token_uri' => env('FIREBASE_TOKEN_URI', 'https://oauth2.googleapis.com/token'),

    'auth_provider_x509_cert_url' => env('FIREBASE_AUTH_PROVIDER_X509_CERT_URL', 'https://www.googleapis.com/oauth2/v1/certs'),

    'client_x509_cert_url' => env('FIREBASE_CLIENT_X509_CERT_URL'),

    /*
    |--------------------------------------------------------------------------
    | FCM Token Settings
    |--------------------------------------------------------------------------
    |
    | Configure the settings for FCM token generation and validation.
    |
    */

        'fcm_token' => [
        // Token version for algorithm changes
        'version' => env('FCM_TOKEN_VERSION', '1.0'),

        // Whether to log token generation (for debugging)
        'log_generation' => env('FCM_TOKEN_LOG_GENERATION', true),
    ],
];
