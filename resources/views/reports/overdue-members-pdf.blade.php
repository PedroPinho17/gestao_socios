<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <title>Sócios em atraso</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1e293b; }
        h1 { font-size: 18px; margin: 0 0 4px; color: #065f46; }
        .meta { font-size: 10px; color: #64748b; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th {
            background: #ecfdf5;
            color: #065f46;
            text-align: left;
            padding: 8px 6px;
            border-bottom: 2px solid #a7f3d0;
            font-size: 10px;
            text-transform: uppercase;
        }
        td { padding: 7px 6px; border-bottom: 1px solid #e2e8f0; vertical-align: top; }
        tr:nth-child(even) td { background: #f8fafc; }
        .num { text-align: right; white-space: nowrap; }
        .empty { padding: 24px; text-align: center; color: #64748b; }
    </style>
</head>
<body>
    <h1>{{ $settings->nome_clube }}</h1>
    <div class="meta">
        Relatório: sócios com quota em atraso · Gerado em {{ $generatedAt }}
        · Total: {{ $rows->count() }}
    </div>

    @if ($rows->isEmpty())
        <p class="empty">Não há sócios em atraso neste momento.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>N.º</th>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Telefone</th>
                    <th>Plano</th>
                    <th class="num">Dias</th>
                    <th>Vencimento</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $row)
                    <tr>
                        <td>{{ $row['numero'] }}</td>
                        <td>{{ $row['nome'] }}</td>
                        <td>{{ $row['email'] ?? '—' }}</td>
                        <td>{{ $row['telefone'] ?? '—' }}</td>
                        <td>{{ $row['plano'] ?? '—' }}</td>
                        <td class="num">{{ $row['dias_atraso'] }}</td>
                        <td>{{ $row['vencimento'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>
