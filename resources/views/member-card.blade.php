<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cartão — {{ $member->nome }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: system-ui, -apple-system, sans-serif;
            background: #e2e8f0;
            padding: 1rem;
        }
        .toolbar {
            max-width: 28rem;
            margin: 0 auto 1rem;
        }
        .toolbar a, .toolbar button {
            font-size: 0.875rem;
            border-radius: 0.5rem;
            padding: 0.5rem 1rem;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .btn-back { color: #065f46; }
        .btn-print {
            background: #065f46;
            color: #fff;
            border: none;
            font-weight: 600;
        }
        .hint {
            font-size: 0.875rem;
            color: #475569;
            margin-top: 0.5rem;
        }
        .card-wrap {
            display: flex;
            justify-content: center;
            margin-top: 1.5rem;
        }
        .card {
            width: 85.6mm;
            height: 53.98mm;
            border-radius: 0.75rem;
            overflow: hidden;
            color: #fff;
            box-shadow: 0 10px 25px rgba(0,0,0,.15);
            background: linear-gradient(135deg, {{ $settings->card_gradient_from }}, {{ $settings->card_gradient_to }});
            position: relative;
        }
        .card-inner {
            display: flex;
            height: 100%;
            padding: 0.75rem;
        }
        .card-logo {
            width: 38%;
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            border-right: 1px solid {{ $settings->card_accent_color }}33;
            padding-right: 0.5rem;
        }
        .card-logo img { max-height: 3.5rem; max-width: 100%; object-fit: contain; }
        .card-logo-text { font-size: 10px; text-align: center; color: {{ $settings->card_accent_color }}; }
        .card-body {
            flex: 1;
            min-width: 0;
            padding-left: 0.75rem;
            padding-right: 3.5rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .card-label {
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: {{ $settings->card_accent_color }};
            font-weight: 600;
        }
        .card-name {
            font-size: 14px;
            font-weight: 700;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .card-extra {
            font-size: 9px;
            color: {{ $settings->card_accent_color }};
            margin-top: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .card-num {
            font-family: ui-monospace, monospace;
            font-size: 12px;
            color: {{ $settings->card_accent_color }};
            margin-top: 4px;
        }
        .card-meta { font-size: 8px; opacity: 0.85; margin-top: 2px; }
        .card-due {
            font-size: 8px;
            font-weight: 600;
            color: {{ $settings->card_accent_color }};
            margin-top: 2px;
        }
        .card-photo {
            position: absolute;
            bottom: 0.5rem;
            right: 0.5rem;
            width: 3rem;
            height: 3rem;
            border-radius: 4px;
            overflow: hidden;
            background: rgba(255,255,255,.1);
        }
        .card-photo img { width: 100%; height: 100%; object-fit: cover; }
        .card-photo-empty {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            font-size: 8px;
            color: rgba(255,255,255,.5);
        }
        @media print {
            body { background: #fff; padding: 0; }
            .toolbar, .footer { display: none !important; }
            .card-wrap { margin: 0; }
            .card { box-shadow: none; border-radius: 0; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <a class="btn-back" href="{{ \App\Filament\Resources\Members\MemberResource::getUrl('edit', ['record' => $member]) }}">← Ficha do sócio</a>
        <a class="btn-print" href="{{ route('member.card.pdf', $member) }}" style="margin-left:0.5rem">Descarregar PDF</a>
        <button type="button" class="btn-print" onclick="window.print()" style="margin-left:0.5rem">Imprimir cartão</button>
        <p class="hint">Pode descarregar o PDF gerado no servidor ou imprimir pelo browser. Desactive cabeçalhos e rodapés na impressão se aparecer data ou URL.</p>
    </div>

    <div class="card-wrap">
        <div class="card">
            <div class="card-inner">
                <div class="card-logo">
                    @if ($settings->logoUrl())
                        <img src="{{ $settings->logoUrl() }}" alt="">
                    @else
                        <div class="card-logo-text">{{ $settings->nome_clube }}</div>
                    @endif
                </div>
                <div class="card-body">
                    <div class="card-label">{{ $settings->card_titulo }}</div>
                    <div class="card-name">{{ $member->nome }}</div>
                    @if ($settings->show_cargo && filled($member->cargo_cartao))
                        <div class="card-extra">
                            <span style="opacity:.8">{{ $settings->card_campo_extra_label }}:</span>
                            {{ $member->cargo_cartao }}
                        </div>
                    @endif
                    <div class="card-num">N.º {{ $member->numero }}</div>
                    @if ($settings->show_email && filled($member->email))
                        <div class="card-meta">{{ $member->email }}</div>
                    @endif
                    @if ($settings->show_telefone && filled($member->telefone))
                        <div class="card-meta">{{ $member->telefone }}</div>
                    @endif
                    <div class="card-meta">
                        Adesão: {{ $member->data_adesao->format('d/m/Y') }}{{ $member->ativo ? '' : ' · Inativo' }}
                    </div>
                    @if ($vencimentoLinha)
                        <div class="card-due">{{ $vencimentoLinha }}</div>
                    @endif
                </div>
                <div class="card-photo">
                    @if ($member->fotoUrl())
                        <img src="{{ $member->fotoUrl() }}" alt="">
                    @else
                        <div class="card-photo-empty">foto</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <p class="footer" style="text-align:center;font-size:.75rem;color:#64748b;margin-top:1.5rem">{{ $settings->nome_clube }}</p>
</body>
</html>
