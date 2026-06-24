<div class="member-card-root">
    @include('cards.partials._styles')
    <div class="member-card-canvas">
        <div class="member-card member-card--verso">
            <div class="member-card-verso-inner">
                @if ($forExport && ($logoDataUri ?? null))
                    <div class="member-card-verso-logo">
                        <img src="{{ $logoDataUri }}" alt="">
                    </div>
                @elseif (! $forExport && $settings->logoUrl())
                    <div class="member-card-verso-logo">
                        <img src="{{ $settings->logoUrl() }}" alt="">
                    </div>
                @endif
                <div class="member-card-verso-clube">{{ $layout['nome_clube'] }}</div>
                @if ($layout['show_qr_verso'] ?? false)
                    @if ($qrDataUri ?? null)
                        <div class="member-card-verso-qr">
                            <img src="{{ $qrDataUri }}" alt="QR code">
                        </div>
                    @endif
                @endif
                @if (filled($layout['verso_text'] ?? ''))
                    <div class="member-card-verso-text">{!! nl2br(e($layout['verso_text'])) !!}</div>
                @endif
                @if ($layout['show_numero'] ?? true)
                    <div class="member-card-verso-numero">N.º {{ $numeroFormatado }}</div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
    .member-card--verso {
        background: linear-gradient(160deg, {{ $layout['gradient_to'] }}, {{ $layout['gradient_from'] }}) !important;
    }
    .member-card--verso .member-card-verso-inner {
        height: 100%;
        padding: 2.5mm;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        color: {{ $layout['text_color'] ?? '#ffffff' }};
    }
    .member-card--verso .member-card-verso-logo img {
        max-height: 7mm;
        max-width: 100%;
        object-fit: contain;
        margin-bottom: 1mm;
    }
    .member-card--verso .member-card-verso-clube {
        font-size: 7px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: {{ $layout['accent_color'] }};
        margin-bottom: 1.5mm;
    }
    .member-card--verso .member-card-verso-qr {
        background: #fff;
        padding: 1mm;
        border-radius: 1mm;
        margin-bottom: 1.5mm;
        line-height: 0;
    }
    .member-card--verso .member-card-verso-qr img {
        width: 18mm;
        height: 18mm;
        display: block;
    }
    .member-card--verso .member-card-verso-text {
        font-size: 6.5px;
        line-height: 1.4;
        color: {{ $layout['accent_color'] }};
        max-height: 14mm;
        overflow: hidden;
    }
    .member-card--verso .member-card-verso-numero {
        margin-top: 1.5mm;
        font-size: 8px;
        font-family: ui-monospace, Consolas, monospace;
        color: {{ $layout['accent_color'] }};
        font-weight: 600;
    }
</style>
