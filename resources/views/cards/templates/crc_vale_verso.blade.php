@php
    $nomeClube = $layout['nome_clube'] ?? 'Clube';
    $logoUrl = filled($settings->logo_path ?? null) ? route('club.branding.logo') : null;
    $qrVerso = $qrDataUri ?? \App\Support\MemberCardQrCode::forMember($member, $layout);
    $matricula = $numeroFormatado ?? $member->numero;
    $motto = $layout['card_motto'] ?? 'TRADIÇÃO • ESPORTE • CULTURA';
@endphp

<div class="member-card-root member-card-root--crc">
    @include('cards.partials._styles')
    <div class="member-card-canvas">
        <div class="member-card member-card--crc-vale member-card--crc-verso">
            <div class="crc-verso-stripes" aria-hidden="true"></div>

            <div class="crc-verso-inner">
                <div class="crc-verso-logo">
                    @if ($forExport && $logoDataUri)
                        <img src="{{ $logoDataUri }}" alt="">
                    @elseif ($logoUrl)
                        <img src="{{ $logoUrl }}" alt="">
                    @else
                        <span class="crc-verso-logo__text">{{ mb_strtoupper($nomeClube) }}</span>
                    @endif
                </div>

                <div class="crc-verso-clube">{{ mb_strtoupper($nomeClube) }}</div>
                <p class="crc-verso-motto">{{ $motto }}</p>

                @if ($qrVerso)
                    <div class="crc-verso-qr">
                        <img src="{{ $qrVerso }}" alt="QR validação">
                    </div>
                @endif

                @if (filled($layout['verso_text'] ?? ''))
                    <div class="crc-verso-text">{!! nl2br(e($layout['verso_text'])) !!}</div>
                @endif

                @if ($layout['show_numero'] ?? true)
                    <div class="crc-verso-numero">N.º {{ $matricula }}</div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
    .member-card-root--crc .member-card--crc-verso {
        background: linear-gradient(145deg, #0a1f44 0%, #1a4a7a 50%, #4a90c8 100%) !important;
        color: #fff !important;
        position: relative;
        overflow: hidden;
        border-radius: 3.5mm;
    }

    .member-card--crc-verso .crc-verso-stripes {
        position: absolute;
        inset: 0;
        opacity: 0.07;
        background: repeating-linear-gradient(
            -52deg,
            transparent,
            transparent 1.2mm,
            #fff 1.2mm,
            #fff 2.4mm
        );
        pointer-events: none;
    }

    .member-card--crc-verso .crc-verso-inner {
        position: relative;
        z-index: 1;
        height: 100%;
        padding: 2.2mm 3mm;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
    }

    .member-card--crc-verso .crc-verso-logo img {
        max-height: 8.5mm;
        max-width: 26mm;
        object-fit: contain;
        filter: drop-shadow(0 1px 2px rgba(0,0,0,.25));
    }

    .member-card--crc-verso .crc-verso-logo__text {
        font-size: 7px;
        font-weight: 800;
        letter-spacing: 0.08em;
    }

    .member-card--crc-verso .crc-verso-clube {
        margin-top: 0.6mm;
        font-size: 6px;
        font-weight: 800;
        letter-spacing: 0.1em;
        color: rgba(255,255,255,.95);
        text-transform: uppercase;
    }

    .member-card--crc-verso .crc-verso-motto {
        margin: 0.4mm 0 0;
        font-size: 3.5px;
        font-weight: 600;
        letter-spacing: 0.14em;
        color: rgba(255,255,255,.7);
        text-transform: uppercase;
    }

    .member-card--crc-verso .crc-verso-qr {
        margin: 1.8mm 0 1.2mm;
        background: #fff;
        padding: 1.1mm;
        border-radius: 1mm;
        line-height: 0;
        box-shadow: 0 1px 4px rgba(0,0,0,.25);
    }

    .member-card--crc-verso .crc-verso-qr img {
        width: 19mm;
        height: 19mm;
        display: block;
    }

    .member-card--crc-verso .crc-verso-text {
        font-size: 5px;
        line-height: 1.4;
        color: rgba(255,255,255,.85);
        max-width: 70mm;
        max-height: 9mm;
        overflow: hidden;
    }

    .member-card--crc-verso .crc-verso-numero {
        margin-top: 1mm;
        font-size: 7.5px;
        font-weight: 700;
        font-family: ui-monospace, Consolas, monospace;
        letter-spacing: 0.06em;
        color: #fff;
    }
</style>
