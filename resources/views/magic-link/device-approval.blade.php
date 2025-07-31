<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Device Approval</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f9fafb; color: #222; margin: 0; padding: 0; }
        .container { max-width: 400px; margin: 60px auto; background: #fff; border-radius: 8px; box-shadow: 0 2px 8px #0001; padding: 32px 24px; text-align: center; }
        .success { color: #16a34a; font-size: 2.5rem; margin-bottom: 16px; }
        .error { color: #dc2626; font-size: 2.5rem; margin-bottom: 16px; }
        h1 { font-size: 1.5rem; margin-bottom: 12px; }
        p { font-size: 1.1rem; }
    </style>
</head>
<body>
    <div class="container">
        @if($success)
            <div class="success">&#10003;</div>
            <h1>Device Approved</h1>
            <p>{{ $message ?? 'Device approved, please return to the app on your device.' }}</p>

            @if(isset($fcm_token))
                <div style="margin-top: 24px; padding: 16px; background: #f3f4f6; border-radius: 6px; text-align: left;">
                    <h3 style="margin: 0 0 8px 0; font-size: 0.9rem; color: #6b7280;">FCM Authentication Token</h3>
                    <p style="margin: 0; font-size: 0.8rem; color: #374151; word-break: break-all; font-family: monospace;">{{ $fcm_token }}</p>
                </div>
                <p style="font-size: 0.9rem; color: #6b7280; margin-top: 16px;">
                    This token can be used for Firebase Cloud Messaging authentication.
                </p>
            @endif
        @else
            <div class="error">&#10007;</div>
            <h1>Device Approval Failed</h1>
            <p>{{ $message ?? 'This device approval link is invalid or expired.' }}</p>
        @endif
    </div>
</body>
</html>
