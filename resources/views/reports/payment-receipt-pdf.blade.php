<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <title>Comprovativo de pagamento {{ $numeroRecibo }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1e293b; }
        .header { border-bottom: 3px solid #10b981; padding-bottom: 12px; margin-bottom: 24px; }
        .club { font-size: 20px; font-weight: bold; color: #065f46; }
        .doc-title { font-size: 13px; text-transform: uppercase; letter-spacing: 1px; color: #64748b; margin-top: 2px; }
        .recibo-no { float: right; text-align: right; font-size: 11px; color: #64748b; }
        .recibo-no strong { display: block; font-size: 15px; color: #0f172a; }
        .section-title { font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; color: #94a3b8; margin: 20px 0 6px; }
        table.kv { width: 100%; border-collapse: collapse; }
        table.kv td { padding: 6px 4px; border-bottom: 1px solid #e2e8f0; vertical-align: top; }
        table.kv td.label { width: 35%; color: #64748b; }
        table.kv td.value { color: #0f172a; font-weight: bold; }
        .amount-box {
            margin-top: 24px;
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            border-radius: 8px;
            padding: 16px 20px;
        }
        .amount-box .label { font-size: 11px; color: #065f46; text-transform: uppercase; letter-spacing: 0.5px; }
        .amount-box .value { font-size: 26px; font-weight: bold; color: #065f46; }
        .notas { margin-top: 18px; font-size: 11px; color: #475569; }
        .footer { margin-top: 40px; font-size: 9px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 8px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="recibo-no">
            Recibo n.º
            <strong>{{ $numeroRecibo }}</strong>
        </div>
        <div class="club">{{ $settings->nome_clube }}</div>
        <div class="doc-title">Comprovativo de pagamento de quota</div>
    </div>

    <div class="section-title">Sócio</div>
    <table class="kv">
        <tr>
            <td class="label">Nome</td>
            <td class="value">{{ $member->nome }}</td>
        </tr>
        <tr>
            <td class="label">N.º de sócio</td>
            <td class="value">{{ $member->numero }}</td>
        </tr>
        @if ($member->email)
            <tr>
                <td class="label">Email</td>
                <td class="value">{{ $member->email }}</td>
            </tr>
        @endif
        @if ($member->quotaPlan)
            <tr>
                <td class="label">Plano de quota</td>
                <td class="value">{{ $member->quotaPlan->nome }}</td>
            </tr>
        @endif
    </table>

    <div class="section-title">Pagamento</div>
    <table class="kv">
        <tr>
            <td class="label">Data</td>
            <td class="value">{{ $payment->data?->format('d/m/Y') ?? '—' }}</td>
        </tr>
        <tr>
            <td class="label">Referência</td>
            <td class="value">{{ $payment->referencia ?? '—' }}</td>
        </tr>
    </table>

    <div class="amount-box">
        <div class="label">Valor pago</div>
        <div class="value">{{ number_format((float) $payment->valor, 2, ',', ' ') }} €</div>
    </div>

    @if ($payment->notas)
        <div class="notas">
            <strong>Notas:</strong> {{ $payment->notas }}
        </div>
    @endif

    <div class="footer">
        Documento gerado em {{ $generatedAt }} por {{ $settings->nome_clube }}.
        Este comprovativo confirma o registo do pagamento acima indicado.
    </div>
</body>
</html>
