<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verso — {{ $member->nome }}</title>
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
        <a class="btn-back" href="{{ route('member.card', $member) }}">← Frente do cartão</a>
        <a class="btn-action" href="{{ route('member.card.pdf', $member) }}">PDF (frente + verso)</a>
        <a class="btn-secondary" href="{{ route('member.card.png.verso', $member) }}">PNG verso 300 DPI</a>
        <button type="button" class="btn-action" onclick="window.print()">Imprimir</button>
        <p class="hint">Verso do cartão CR80. Com QR ativo, a leitura abre a página pública de validação do sócio.</p>
    </div>

    <div class="card-wrap">
        @php $versoTemplate = \App\Support\MemberCardLayout::versoTemplate($layout); @endphp
        @include('cards.templates.'.$versoTemplate)
    </div>

    <p class="footer" style="text-align:center;font-size:.75rem;color:#64748b;margin-top:1.5rem">{{ $layout['nome_clube'] }} — verso</p>
</body>
</html>
