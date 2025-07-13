<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Remove Password - {{ config('app.name') }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .button {
            display: inline-block;
            background-color: #dc2626;
            color: white;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 6px;
            font-weight: 500;
            margin: 20px 0;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            font-size: 14px;
            color: #6b7280;
        }
        .warning {
            background-color: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 6px;
            padding: 16px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Remove Password - {{ config('app.name') }}</h1>
    </div>

    <p>Hello!</p>

    <p>You requested to remove your password and switch to magic link authentication only.</p>

    <div class="warning">
        <strong>⚠️ Important:</strong> After clicking the button below, you will no longer be able to sign in with a password. You'll only be able to sign in using magic links sent to your email.
    </div>

    <div style="text-align: center;">
        <a href="{{ route('forgot-password.verify', $token) }}" class="button">
            Remove Password & Switch to Magic Links
        </a>
    </div>

    <p>This link will expire in 15 minutes and can only be used once.</p>

    <p>If you didn't request this change, you can safely ignore this email. Your password will remain unchanged.</p>

    <div class="footer">
        <p>This email was sent to you because you requested to remove your password from your account.</p>
        <p>If you have any questions, please contact our support team.</p>
    </div>
</body>
</html>
