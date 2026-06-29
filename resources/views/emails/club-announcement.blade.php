<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $settings->nome_clube }}</title>
</head>
<body style="margin:0; padding:0; background:#f1f5f9; font-family: Arial, Helvetica, sans-serif; color:#1e293b;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9; padding:24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px; width:100%; background:#ffffff; border-radius:10px; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,0.1);">
                    <tr>
                        <td style="background:#065f46; padding:24px 32px;">
                            <div style="color:#ffffff; font-size:20px; font-weight:bold;">{{ $settings->nome_clube }}</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px;">
                            <p style="margin:0 0 16px; font-size:15px;">Olá {{ $member->nome }},</p>
                            <div style="font-size:14px; line-height:1.6; color:#334155;">
                                {!! $corpoHtml !!}
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="background:#f8fafc; padding:16px 32px; text-align:center; font-size:11px; color:#94a3b8;">
                            {{ $settings->nome_clube }} · Esta mensagem foi enviada para sócios do clube.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
