<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="color-scheme" content="light">
<title>{{ $title }}</title>
</head>
<body style="margin:0;padding:0;background:#f2f6fc;font-family:'Segoe UI',Helvetica,Arial,sans-serif;color:#334155;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f2f6fc;padding:32px 12px;">
<tr><td align="center">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;">
        <tr><td style="padding:0 4px 18px 4px;">
            <table role="presentation" cellpadding="0" cellspacing="0"><tr>
                <td style="width:34px;height:34px;background:#172a4d;border-radius:10px;text-align:center;color:#fff;font-weight:800;font-size:18px;line-height:34px;">+</td>
                <td style="padding-left:10px;font-size:18px;font-weight:700;color:#172a4d;font-family:Georgia,serif;">{{ setting('site_name') }}</td>
            </tr></table>
        </td></tr>
        <tr><td style="background:#ffffff;border-radius:20px;padding:32px 28px;box-shadow:0 8px 24px -12px rgba(23,42,77,.25);">
            @if(!empty($name))<p style="margin:0 0 6px;font-size:14px;color:#64748b;">Halo {{ $name }},</p>@endif
            <h1 style="margin:0 0 14px;font-size:22px;line-height:1.3;color:#172a4d;">{{ $title }}</h1>
            <p style="margin:0 0 24px;font-size:15px;line-height:1.65;color:#334155;">{!! nl2br(e($body)) !!}</p>
            @if(!empty($url))
                <table role="presentation" cellpadding="0" cellspacing="0"><tr><td style="background:#1d55f0;border-radius:12px;">
                    <a href="{{ $url }}" style="display:inline-block;padding:13px 24px;font-size:15px;font-weight:700;color:#ffffff;text-decoration:none;">{{ $actionText ?: 'Buka' }} →</a>
                </td></tr></table>
                <p style="margin:20px 0 0;font-size:12px;color:#94a3b8;line-height:1.5;">Jika tombol tidak berfungsi, salin tautan ini ke browser:<br><a href="{{ $url }}" style="color:#1d55f0;word-break:break-all;">{{ $url }}</a></p>
            @endif
        </td></tr>
        <tr><td style="padding:20px 8px;text-align:center;font-size:12px;color:#94a3b8;line-height:1.6;">
            {{ setting('site_tagline') }}<br>
            @if(setting('contact_whatsapp'))WhatsApp: +{{ normalize_phone(setting('contact_whatsapp')) }} · @endif<a href="{{ url('/') }}" style="color:#64748b;">{{ parse_url(url('/'), PHP_URL_HOST) }}</a><br>
            Email ini dikirim otomatis, mohon tidak membalas.
        </td></tr>
    </table>
</td></tr>
</table>
</body>
</html>
