<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <title>Cartão — {{ $member->nome }}</title>
    <style>
        @page { margin: 0; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: DejaVu Sans, sans-serif;
            width: 85.6mm;
            height: 53.98mm;
        }
        .card {
            width: 85.6mm;
            height: 53.98mm;
            color: #fff;
            background: linear-gradient(135deg, {{ $settings->card_gradient_from }}, {{ $settings->card_gradient_to }});
            position: relative;
            overflow: hidden;
        }
        .card-inner {
            display: table;
            width: 100%;
            height: 53.98mm;
        }
        .card-logo {
            display: table-cell;
            width: 38%;
            vertical-align: middle;
            text-align: center;
            border-right: 1px solid {{ $settings->card_accent_color }};
            padding: 6px;
        }
        .card-logo img { max-height: 38px; max-width: 90%; }
        .card-logo-text { font-size: 9px; color: {{ $settings->card_accent_color }}; }
        .card-body {
            display: table-cell;
            vertical-align: middle;
            padding: 8px 44px 8px 10px;
        }
        .card-label {
            font-size: 7px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: {{ $settings->card_accent_color }};
            font-weight: bold;
        }
        .card-name {
            font-size: 13px;
            font-weight: bold;
            margin-top: 2px;
            white-space: nowrap;
            overflow: hidden;
        }
        .card-extra {
            font-size: 8px;
            color: {{ $settings->card_accent_color }};
            margin-top: 2px;
        }
        .card-num {
            font-family: DejaVu Sans Mono, monospace;
            font-size: 11px;
            color: {{ $settings->card_accent_color }};
            margin-top: 3px;
        }
        .card-meta { font-size: 7px; margin-top: 2px; opacity: 0.9; }
        .card-due {
            font-size: 7px;
            font-weight: bold;
            color: {{ $settings->card_accent_color }};
            margin-top: 2px;
        }
        .card-photo {
            position: absolute;
            bottom: 6px;
            right: 6px;
            width: 28px;
            height: 28px;
            overflow: hidden;
            background: rgba(255,255,255,.15);
        }
        .card-photo img { width: 28px; height: 28px; }
    </style>
</head>
<body>
    <div class="card">
        <div class="card-inner">
            <div class="card-logo">
                @if ($logoDataUri)
                    <img src="{{ $logoDataUri }}" alt="">
                @else
                    <div class="card-logo-text">{{ $settings->nome_clube }}</div>
                @endif
            </div>
            <div class="card-body">
                <div class="card-label">{{ $settings->card_titulo }}</div>
                <div class="card-name">{{ $member->nome }}</div>
                @if ($settings->show_cargo && filled($member->cargo_cartao))
                    <div class="card-extra">{{ $settings->card_campo_extra_label }}: {{ $member->cargo_cartao }}</div>
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
            @if ($fotoDataUri)
                <div class="card-photo">
                    <img src="{{ $fotoDataUri }}" alt="">
                </div>
            @endif
        </div>
    </div>
</body>
</html>
