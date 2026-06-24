@php
    use App\Support\MemberCardDimensions;

    $bleedPt = MemberCardDimensions::BLEED_MM * MemberCardDimensions::MM_TO_PT;
    [$pageW, $pageH] = MemberCardDimensions::paperPoints(withBleed: true);
    $cardW = MemberCardDimensions::WIDTH_MM * MemberCardDimensions::MM_TO_PT;
    $cardH = MemberCardDimensions::HEIGHT_MM * MemberCardDimensions::MM_TO_PT;

    $bgFrom = $layout['gradient_from'];
    $bgTo = $layout['gradient_to'];
    $accent = $layout['accent_color'];
    $text = $layout['text_color'] ?? '#ffffff';
    $font = 'DejaVu Sans, sans-serif';
@endphp
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Cartão — {{ $member->nome }}</title>
    <style>
        @page { margin: 0; size: {{ $pageW }}pt {{ $pageH }}pt; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            margin: 0;
            padding: {{ $bleedPt }}pt;
            width: {{ $pageW }}pt;
            height: {{ $pageH }}pt;
            font-family: {{ $font }};
            background: #ffffff;
        }
        table { border-collapse: collapse; }
        .card-table {
            width: {{ $cardW }}pt;
            height: {{ $cardH }}pt;
            background-color: {{ $bgFrom }};
            color: {{ $text }};
            table-layout: fixed;
        }
        .logo-cell {
            width: 34%;
            text-align: center;
            vertical-align: middle;
            border-right: 1px solid {{ $accent }};
            padding: 6pt 4pt;
        }
        .body-cell {
            vertical-align: middle;
            padding: 6pt 8pt 6pt 8pt;
        }
        .photo-cell {
            width: 34pt;
            vertical-align: bottom;
            padding: 0 6pt 6pt 0;
        }
        .label {
            font-size: 7pt;
            text-transform: uppercase;
            letter-spacing: 0.5pt;
            color: {{ $accent }};
            font-weight: bold;
        }
        .name {
            font-size: 12pt;
            font-weight: bold;
            color: {{ $text }};
            margin-top: 2pt;
        }
        .line {
            font-size: 7.5pt;
            color: {{ $accent }};
            margin-top: 2pt;
        }
        .numero {
            font-size: 10pt;
            font-weight: bold;
            color: {{ $accent }};
            margin-top: 3pt;
        }
        .due {
            font-size: 7pt;
            font-weight: bold;
            color: {{ $accent }};
            margin-top: 2pt;
        }
        .footer {
            font-size: 6.5pt;
            color: {{ $accent }};
            margin-top: 4pt;
        }
        .logo-text {
            font-size: 8pt;
            color: {{ $accent }};
            text-align: center;
        }
        .photo-box {
            width: 30pt;
            height: 30pt;
            background-color: rgba(255,255,255,0.12);
            text-align: center;
            vertical-align: middle;
        }
        .photo-box img {
            width: 30pt;
            height: 30pt;
        }
        .club-fallback {
            background-color: {{ $bgTo }};
        }
    </style>
</head>
<body>
<table class="card-table" cellpadding="0" cellspacing="0">
    <tr>
        <td class="logo-cell">
            @if ($logoDataUri)
                <img src="{{ $logoDataUri }}" alt="" style="max-height:36pt; max-width:100%;">
            @else
                <div class="logo-text">{{ $layout['nome_clube'] }}</div>
            @endif
        </td>
        <td class="body-cell">
            <div class="label">{{ $layout['card_titulo'] }}</div>

            @if ($layout['show_nome'] ?? true)
                <div class="name">{{ $member->nome }}</div>
            @endif

            @if (($layout['show_cargo'] ?? true) && filled($member->cargo_cartao))
                <div class="line">{{ $layout['cargo_label'] }}: {{ $member->cargo_cartao }}</div>
            @endif

            @if ($layout['show_numero'] ?? true)
                <div class="numero">N.º {{ $numeroFormatado }}</div>
            @endif

            @if ($planoLinha)
                <div class="line">{{ $planoLinha }}</div>
            @endif

            @if (($layout['show_email'] ?? false) && filled($member->email))
                <div class="line">{{ $member->email }}</div>
            @endif

            @if (($layout['show_telefone'] ?? false) && filled($member->telefone))
                <div class="line">{{ $member->telefone }}</div>
            @endif

            @if ($layout['show_adesao'] ?? false)
                <div class="line">
                    Adesão: {{ $member->data_adesao->format('d/m/Y') }}{{ $member->ativo ? '' : ' · Inativo' }}
                </div>
            @endif

            @if ($vencimentoLinha)
                <div class="due">{{ $vencimentoLinha }}</div>
            @endif

            @if (filled($layout['footer_text'] ?? ''))
                <div class="footer">{{ $layout['footer_text'] }}</div>
            @endif
        </td>
        @if ($layout['show_foto'] ?? true)
            <td class="photo-cell">
                <div class="photo-box">
                    @if ($fotoDataUri)
                        <img src="{{ $fotoDataUri }}" alt="">
                    @endif
                </div>
            </td>
        @endif
    </tr>
</table>
</body>
</html>
