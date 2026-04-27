<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $appName }} — your new password</title>
</head>
<body style="margin:0; padding:0; background:#f4f5f7; font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; color:#1f2937; line-height:1.5;">
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background:#f4f5f7; padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="560" style="max-width:560px; background:#ffffff; border-radius:18px; box-shadow:0 4px 12px rgba(4,6,12,0.08); overflow:hidden;">
                    <tr>
                        <td style="background:linear-gradient(135deg, #04060c 0%, #0a0e18 100%); padding:36px 40px; text-align:center;">
                            @if($appUrl)
                                <img src="{{ rtrim($appUrl, '/') }}/vision-logo.png" alt="{{ $appName }}" height="56" style="display:inline-block; height:56px; width:auto; margin:0 auto;" />
                            @else
                                <h1 style="margin:0; color:#44e7da; font-size:28px; font-weight:600; letter-spacing:-0.01em;">{{ $appName }}</h1>
                            @endif
                            <p style="margin:14px 0 0; color:#a8b2c8; font-size:13px; letter-spacing:0.04em; text-transform:uppercase;">New password</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:36px 40px;">
                            <p style="margin:0 0 16px; font-size:16px; color:#1f2937;">Hi {{ $userName }},</p>
                            <p style="margin:0 0 24px; font-size:15px; color:#374151;">
                                A new password has been generated for your account ({{ $userEmail }}). Use it on your next sign-in. After signing in, please change it under your profile.
                            </p>
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background:#f9fafb; border:1px solid #e5e7eb; border-left:3px solid #44e7da; border-radius:10px; margin:0 0 24px;">
                                <tr>
                                    <td style="padding:22px 26px; text-align:center;">
                                        <p style="margin:0 0 6px; font-size:11px; text-transform:uppercase; letter-spacing:0.08em; color:#6b7280;">Temporary password</p>
                                        <p style="margin:0; font-family:'JetBrains Mono', 'SFMono-Regular', Menlo, Consolas, monospace; font-size:22px; font-weight:600; color:#04060c; letter-spacing:0.04em;">{{ $temporaryPassword }}</p>
                                    </td>
                                </tr>
                            </table>
                            @if($appUrl)
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin:0 0 24px;">
                                    <tr>
                                        <td style="border-radius:10px; background:#44e7da;">
                                            <a href="{{ $appUrl }}" style="display:inline-block; padding:12px 28px; font-size:14px; font-weight:600; color:#04060c; text-decoration:none; letter-spacing:0.02em;">Sign in to {{ $appName }}</a>
                                        </td>
                                    </tr>
                                </table>
                            @endif
                            <p style="margin:0 0 8px; font-size:13px; color:#6b7280;">
                                For security, all your previous sessions have been signed out.
                            </p>
                            <p style="margin:0; font-size:13px; color:#6b7280;">
                                Didn't request a reset? Contact your administrator.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:18px 40px; background:#f9fafb; border-top:1px solid #e5e7eb;">
                            <p style="margin:0; font-size:12px; color:#9ca3af; text-align:center;">
                                @if($appUrl)
                                    <a href="{{ $appUrl }}" style="color:#04060c; text-decoration:none; font-weight:500;">{{ parse_url($appUrl, PHP_URL_HOST) ?: $appUrl }}</a> ·
                                @endif
                                {{ $appName }}
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
