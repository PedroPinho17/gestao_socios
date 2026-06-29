<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprovativo de pagamento</title>
</head>
<body style="margin:0; padding:0; background:#f1f5f9; font-family: Arial, Helvetica, sans-serif; color:#1e293b;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9; padding:24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px; width:100%; background:#ffffff; border-radius:10px; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,0.1);">
                    <tr>
                        <td style="background:#065f46; padding:24px 32px;">
                            <div style="color:#ffffff; font-size:20px; font-weight:bold;">{{ $settings->nome_clube }}</div>
                            <div style="color:#a7f3d0; font-size:13px; margin-top:4px;">Comprovativo de pagamento de quota</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px;">
                            <p style="margin:0 0 16px; font-size:15px;">Olá {{ $member->nome }},</p>
                            <p style="margin:0 0 20px; font-size:14px; line-height:1.6; color:#475569;">
                                Confirmamos o registo do seu pagamento de quota. Em anexo encontra o comprovativo em PDF.
                            </p>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #e2e8f0; border-radius:8px;">
                                <tr>
                                    <td style="padding:12px 16px; border-bottom:1px solid #e2e8f0; font-size:13px; color:#64748b;">Data</td>
                                    <td style="padding:12px 16px; border-bottom:1px solid #e2e8f0; font-size:13px; font-weight:bold; text-align:right;">{{ $payment->data?->format('d/m/Y') ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:12px 16px; border-bottom:1px solid #e2e8f0; font-size:13px; color:#64748b;">Referência</td>
                                    <td style="padding:12px 16px; border-bottom:1px solid #e2e8f0; font-size:13px; font-weight:bold; text-align:right;">{{ $payment->referencia ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:12px 16px; font-size:13px; color:#64748b;">Valor pago</td>
                                    <td style="padding:12px 16px; font-size:16px; font-weight:bold; text-align:right; color:#065f46;">{{ number_format((float) $payment->valor, 2, ',', ' ') }} €</td>
                                </tr>
                            </table>

                            <p style="margin:24px 0 0; font-size:12px; color:#94a3b8; line-height:1.6;">
                                Este é um email automático. Em caso de dúvida, contacte o clube.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="background:#f8fafc; padding:16px 32px; text-align:center; font-size:11px; color:#94a3b8;">
                            {{ $settings->nome_clube }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
