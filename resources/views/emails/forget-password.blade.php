<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('messages.Password reset code') }}</title>
</head>
<body style="margin:0;padding:0;background:#f4f4f5;font-family:system-ui,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="padding:40px 0;">
    <tr>
        <td align="center">
            <table width="560" cellpadding="0" cellspacing="0" style="background:#fff;border-radius:8px;overflow:hidden;">
                <tr>
                    <td style="padding:32px;color:#3f3f46;text-align:center;">
                        <p>{{ __('messages.Use this code to reset your password') }}</p>
                        <p style="font-size:32px;font-weight:700;letter-spacing:8px;color:#18181b;margin:24px 0;">
                            {{ $code }}
                        </p>
                        <p style="font-size:14px;color:#71717a;">{{ __('messages.Code expires in 10 minutes') }}</p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
