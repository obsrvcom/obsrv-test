<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign in to {{ config('app.name') }}</title>
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
            background-color: #3b82f6;
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
    </style>
</head>
<body>
    @if(isset($isInvitation) && $isInvitation)
        <div class="header">
            <h1>You've been invited to join Obsrv!</h1>
        </div>
        <p>Hello!</p>
        <p>
            You have been invited to join <strong>Obsrv</strong>@if(isset($siteName) && $siteName), and to have access to the site <strong>{{ $siteName }}</strong>@elseif(isset($companyName) && $companyName), and to join the company <strong>{{ $companyName }}</strong>@endif.<br>
            Click the button below to create your account and accept the invitation:
        </p>
        <div style="text-align: center;">
            <a href="{{ route('magic-link.verify', $token) }}@if(isset($site) && $site){{ '?redirect=/app/site/' . $site->id . '/users' }}@elseif(isset($company) && $company){{ '?redirect=/app/company/' . $company->id . '/users' }}@endif" class="button">
                Accept Invitation
            </a>
        </div>
        <p>This link will expire in 15 minutes and can only be used once.</p>
        <p>If you weren't expecting this invitation, you can safely ignore this email.</p>
    @else
        <div class="header">
            <h1>Sign in to {{ config('app.name') }}</h1>
        </div>
        <p>Hello!</p>
        <p>You requested a magic link to sign in to your account. Click the button below to sign in:</p>
        <div style="text-align: center;">
            <a href="{{ route('magic-link.verify', $token) }}@if(isset($site) && $site){{ '?redirect=/app/site/' . $site->id . '/users' }}@elseif(isset($company) && $company){{ '?redirect=/app/company/' . $company->id . '/users' }}@endif" class="button">
                Sign in to {{ config('app.name') }}
            </a>
        </div>
        <p>This link will expire in 15 minutes and can only be used once.</p>
        <p>If you didn't request this link, you can safely ignore this email.</p>
    @endif

    <div class="footer">
        <p>This email was sent to you because someone requested a magic link to sign in to your account.</p>
        <p>If you have any questions, please contact our support team.</p>
    </div>
</body>
</html>
