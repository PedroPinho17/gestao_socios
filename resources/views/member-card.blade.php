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
            max-width: 32rem;
            margin: 0 auto 1rem;
        }
        .toolbar a, .toolbar button {
            font-size: 0.875rem;
            border-radius: 0.5rem;
            padding: 0.5rem 1rem;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin: 0 .25rem .25rem 0;
        }
        .btn-back { color: #065f46; }
        .btn-action {
            background: #065f46;
            color: #fff;
            border: none;
            font-weight: 600;
        }
        .btn-secondary {
            background: #fff;
            color: #065f46;
            border: 1px solid #065f46;
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
        @media print {
            body { background: #fff; padding: 0; }
            .toolbar, .footer { display: none !important; }
            .card-wrap { margin: 0; }
            .member-card { box-shadow: none !important; border-radius: 0 !important; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <a class="btn-back" href="{{ \App\Filament\Resources\Members\MemberResource::getUrl('edit', ['record' => $member]) }}">← Ficha do sócio</a>
        <a class="btn-action" href="{{ route('member.card.pdf', $member) }}">PDF (gráfica)</a>
        <a class="btn-secondary" href="{{ route('member.card.png', $member) }}">PNG 300 DPI</a>
        @if (\App\Support\MemberCardLayout::hasVerso($layout))
            <a class="btn-secondary" href="{{ route('member.card.verso', $member) }}">Ver verso</a>
        @endif
        <button type="button" class="btn-action" onclick="window.print()">Imprimir</button>
        <p class="hint">
            Tamanho CR80 ({{ \App\Support\MemberCardDimensions::WIDTH_MM }}×{{ \App\Support\MemberCardDimensions::HEIGHT_MM }} mm).
            PDF inclui sangria de {{ \App\Support\MemberCardDimensions::BLEED_MM }} mm para corte
            @if (\App\Support\MemberCardLayout::hasVerso($layout))
                e segunda página com o verso.
            @endif
            PNG em {{ \App\Support\MemberCardDimensions::DPI }} DPI para gráfica ou Evolis/Zebra.
        </p>
    </div>

    <div class="card-wrap">
        @php
            $template = $layout['template'] ?? 'classic';
            if (! view()->exists('cards.templates.'.$template)) {
                $template = 'classic';
            }
        @endphp
        @include('cards.templates.'.$template)
    </div>

    <p class="footer" style="text-align:center;font-size:.75rem;color:#64748b;margin-top:1.5rem">{{ $layout['nome_clube'] }}</p>
</body>
</html>
