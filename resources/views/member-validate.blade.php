<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Validação — {{ $member->nome }} · {{ $clubName }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            background: linear-gradient(160deg, {{ $gradientFrom }}22, {{ $gradientTo }}18), #f1f5f9;
            color: #0f172a;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.25rem;
        }
        .card {
            width: 100%;
            max-width: 26rem;
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 10px 30px rgba(15, 23, 42, .08);
            overflow: hidden;
            border-top: 4px solid {{ $primaryColor }};
        }
        .header {
            padding: 1.5rem 1.5rem 1.25rem;
            background: linear-gradient(135deg, {{ $gradientFrom }}, {{ $gradientTo }});
            color: #fff;
            text-align: center;
        }
        .header-logo {
            margin: 0 auto 1rem;
            max-height: 4.5rem;
            max-width: 100%;
            object-fit: contain;
            filter: drop-shadow(0 2px 6px rgba(0,0,0,.2));
        }
        .header h1 {
            margin: 0 0 .25rem;
            font-size: 1.125rem;
        }
        .header p {
            margin: 0;
            opacity: .88;
            font-size: .875rem;
            color: {{ $accentColor }};
        }
        .body { padding: 1.25rem 1.5rem 1.5rem; }
        .status {
            display: inline-block;
            padding: .35rem .75rem;
            border-radius: 999px;
            font-size: .8125rem;
            font-weight: 600;
            margin-bottom: 1rem;
            border: 1px solid transparent;
        }
        .status.ok { background: #dcfce7; color: #166534; border-color: #86efac; }
        .status.warn { background: #fef9c3; color: #854d0e; border-color: #fde047; }
        .status.bad { background: #fee2e2; color: #991b1b; border-color: #fca5a5; }
        .status.neutral { background: #e2e8f0; color: #334155; border-color: #cbd5e1; }
        dl { margin: 0; }
        dt {
            font-size: .75rem;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: #64748b;
            margin-top: .85rem;
        }
        dt:first-child { margin-top: 0; }
        dd {
            margin: .2rem 0 0;
            font-size: 1rem;
            font-weight: 600;
        }
        .footer {
            margin-top: 1.25rem;
            font-size: .75rem;
            color: #64748b;
            line-height: 1.4;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            @if ($logoUrl)
                <img class="header-logo" src="{{ $logoUrl }}" alt="{{ $clubName }}">
            @endif
            <h1>{{ $clubName }}</h1>
            <p>Validação de cartão de sócio</p>
        </div>
        <div class="body">
            <span class="status {{ $statusTone }}">{{ $statusLabel }}</span>
            <dl>
                <dt>Nome</dt>
                <dd>{{ $member->nome }}</dd>
                <dt>N.º de sócio</dt>
                <dd>{{ $member->numero }}</dd>
                @if ($member->quotaPlan)
                    <dt>Plano</dt>
                    <dd>{{ $member->quotaPlan->nome }}</dd>
                @endif
                @if ($situation['next_due'] && $member->ativo)
                    <dt>Próximo vencimento</dt>
                    <dd>{{ $situation['next_due']->format('d/m/Y') }}</dd>
                @endif
            </dl>
            <p class="footer">
                Esta página confirma que o cartão pertence a um sócio registado.
                Não inclui dados sensíveis. O link é assinado digitalmente pelo clube.
            </p>
        </div>
    </div>
</body>
</html>
