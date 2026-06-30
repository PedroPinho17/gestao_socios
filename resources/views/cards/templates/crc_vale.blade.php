@php
    use App\Support\ClubBranding;

    $nomeClube = $layout['nome_clube'] ?? 'Clube';
    $partesNome = preg_split('/\s+/u', trim($nomeClube), -1, PREG_SPLIT_NO_EMPTY) ?: [$nomeClube];
    $ultimaPalavra = array_pop($partesNome);
    $nomePrincipal = $partesNome !== [] ? implode(' ', $partesNome) : '';
    $nomeDestaque = $partesNome !== [] ? $ultimaPalavra : $nomeClube;

    if (preg_match('/^crc\s+vale$/iu', trim($nomeClube))) {
        $nomePrincipal = 'CLUBE RECREATIVO';
        $nomeDestaque = 'VALE';
    }

    $crestUrl = ClubBranding::publicLogoUrl();
    $crestDataUri = ClubBranding::logoDataUri();
    $qrFront = $qrDataUri ?? \App\Support\MemberCardQrCode::forMember($member, $layout);
    $validade = $validadePeriodo ?? now()->format('Y').'/'.(now()->year + 1);
    $motto = $layout['card_motto'] ?? 'TRADIÇÃO • ESPORTE • CULTURA';
    $slogan = $layout['card_slogan'] ?? 'Juntos Somos Mais Fortes';
    $tipoSocio = filled($member->cargo_cartao) ? $member->cargo_cartao : ($layout['card_titulo'] ?? 'Sócio');
    $matricula = $numeroFormatado ?? $member->numero;
    $adesao = $member->data_adesao?->format('d/m/Y') ?? '—';
    $ativo = (bool) $member->ativo;
    $barcodeNum = preg_replace('/\D/', '', $matricula) ?: $matricula;
@endphp

<div class="member-card-root member-card-root--crc">
    @include('cards.partials._styles')
    <div class="member-card-canvas">
        <div class="member-card member-card--crc-vale">
            <div class="crc-stripes-bg" aria-hidden="true"></div>

            {{-- Coluna direita: brasão Vale a ocupar toda a altura --}}
            <section class="crc-right" aria-hidden="false">
                <div class="crc-crest">
                    @if ($forExport && $crestDataUri)
                        <img src="{{ $crestDataUri }}" alt="">
                    @elseif ($crestUrl)
                        <img src="{{ $crestUrl }}" alt="">
                    @else
                        <div class="crc-crest__fallback">{{ mb_strtoupper($nomeClube) }}</div>
                    @endif
                </div>

                <div class="crc-right-foot">
                    <div class="crc-badge {{ $ativo ? 'is-active' : 'is-inactive' }}">
                        {{ $ativo ? 'SÓCIO ATIVO' : 'SÓCIO INATIVO' }}
                    </div>

                    <div class="crc-barcode" aria-hidden="true">
                        <div class="crc-barcode__bars"></div>
                        <span class="crc-barcode__num">{{ $barcodeNum }}</span>
                    </div>

                    <div class="crc-verified">
                        <span class="crc-verified__icon">✓</span>
                        <span>SÓCIO VERIFICADO</span>
                    </div>
                </div>

                <div class="crc-holo" aria-hidden="true"></div>
            </section>

            <header class="crc-header">
                <div class="crc-header__brand">
                    @if ($nomePrincipal !== '')
                        <span class="crc-header__club">{{ mb_strtoupper($nomePrincipal) }}</span>
                    @endif
                    <span class="crc-header__club crc-header__club--xl">{{ mb_strtoupper($nomeDestaque) }}</span>
                    <p class="crc-header__motto">{{ $motto }}</p>
                </div>
            </header>

            <div class="crc-body">
                <section class="crc-navy">
                    <div class="crc-member-row">
                        @if ($layout['show_foto'] ?? true)
                            <div class="crc-photo">
                                @if ($forExport && $fotoDataUri)
                                    <img src="{{ $fotoDataUri }}" alt="">
                                @elseif (! $forExport && $member->fotoUrl())
                                    <img src="{{ $member->fotoUrl() }}" alt="">
                                @else
                                    <div class="crc-photo__empty"></div>
                                @endif
                            </div>
                        @endif
                        <div class="crc-card-type">
                            <div class="crc-card-type__main">CARTÃO SÓCIO</div>
                            <div class="crc-card-type__sub">{{ mb_strtoupper($tipoSocio) }}</div>
                        </div>
                    </div>

                    @if ($layout['show_nome'] ?? true)
                        <div class="crc-field">
                            <span class="crc-label">NOME</span>
                            <span class="crc-value crc-value--name">{{ mb_strtoupper($member->nome) }}</span>
                        </div>
                    @endif

                    <div class="crc-meta">
                        @if ($layout['show_numero'] ?? true)
                            <div class="crc-meta__col">
                                <span class="crc-label">MATRÍCULA</span>
                                <span class="crc-value">{{ $matricula }}</span>
                            </div>
                        @endif
                        @if ($layout['show_adesao'] ?? true)
                            <div class="crc-meta__col">
                                <span class="crc-label">DATA DE ADMISSÃO</span>
                                <span class="crc-value">{{ $adesao }}</span>
                            </div>
                        @endif
                    </div>

                    <div class="crc-bottom">
                        @if ($qrFront)
                            <div class="crc-qr">
                                <img src="{{ $qrFront }}" alt="QR">
                            </div>
                        @endif
                        @if ($layout['show_validade'] ?? true)
                            <div class="crc-validade">
                                <span class="crc-validade__icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" width="9" height="9" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="3" y="4" width="18" height="18" rx="2"/>
                                        <line x1="16" y1="2" x2="16" y2="6"/>
                                        <line x1="8" y1="2" x2="8" y2="6"/>
                                        <line x1="3" y1="10" x2="21" y2="10"/>
                                    </svg>
                                </span>
                                <span class="crc-validade__text">
                                    VALIDADE
                                    <strong>{{ $validade }}</strong>
                                </span>
                            </div>
                        @endif
                    </div>

                    <footer class="crc-slogan">
                        <em>{{ $slogan }}</em>
                        <span>{{ mb_strtoupper($nomeClube) }}</span>
                    </footer>
                </section>
            </div>
        </div>
    </div>
</div>

<style>
    .member-card-root--crc {
        --crc-navy: #0a1f44;
        --crc-blue: #4a90c8;
        --crc-sky: #e8f4fc;
        --crc-stripe: #d4e8f5;
    }

    .member-card-root--crc .member-card {
        background: var(--crc-sky) !important;
        color: var(--crc-navy) !important;
        box-shadow: 0 2px 10px rgba(10, 31, 68, .15) !important;
    }

    .member-card--crc-vale {
        display: flex;
        flex-direction: column;
        border-radius: 3.5mm;
        overflow: hidden;
        position: relative;
    }

    .member-card--crc-vale .crc-stripes-bg {
        position: absolute;
        inset: 0;
        z-index: 0;
        background: repeating-linear-gradient(
            -52deg,
            var(--crc-sky),
            var(--crc-sky) 1.2mm,
            var(--crc-stripe) 1.2mm,
            var(--crc-stripe) 2.4mm
        );
    }

    .member-card--crc-vale .crc-header {
        position: relative;
        z-index: 3;
        display: flex;
        justify-content: center;
        align-items: flex-start;
        padding: 1.6mm 0 0.8mm;
        min-height: 11mm;
        background: transparent;
    }

    .member-card--crc-vale .crc-header__brand {
        flex: 0 0 58%;
        text-align: center;
        padding: 0 1.5mm;
    }

    .member-card--crc-vale .crc-header__club {
        display: block;
        font-size: 5.5px;
        font-weight: 800;
        letter-spacing: 0.1em;
        color: var(--crc-navy);
        line-height: 1.05;
    }

    .member-card--crc-vale .crc-header__club--xl {
        font-size: 12px;
        font-weight: 900;
        letter-spacing: 0.05em;
        margin-top: 0.15mm;
    }

    .member-card--crc-vale .crc-header__motto {
        margin: 0.5mm 0 0;
        font-size: 4px;
        font-weight: 700;
        letter-spacing: 0.12em;
        color: var(--crc-navy);
        text-transform: uppercase;
    }

    .member-card--crc-vale .crc-body {
        position: relative;
        z-index: 2;
        flex: 1;
        display: flex;
        min-height: 0;
    }

    .member-card--crc-vale .crc-navy {
        width: 60%;
        flex-shrink: 0;
        background: var(--crc-navy) !important;
        color: #fff !important;
        padding: 1mm 2mm 1mm 1.8mm;
        display: flex;
        flex-direction: column;
        clip-path: polygon(0 0, 100% 0, 90% 100%, 0 100%);
        position: relative;
        z-index: 2;
    }

    .member-card--crc-vale .crc-member-row {
        display: flex;
        gap: 1.5mm;
        align-items: flex-start;
        margin-bottom: 0.8mm;
        padding-bottom: 0.6mm;
        border-bottom: 0.2mm solid rgba(255,255,255,.18);
    }

    .member-card--crc-vale .crc-photo {
        width: 11.5mm;
        height: 13.5mm;
        flex-shrink: 0;
        border-radius: 0.8mm;
        overflow: hidden;
        border: 0.35mm solid rgba(255,255,255,.45);
        background: rgba(255,255,255,.1);
    }

    .member-card--crc-vale .crc-photo img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .member-card--crc-vale .crc-photo__empty {
        width: 100%;
        height: 100%;
        background: rgba(255,255,255,.08);
    }

    .member-card--crc-vale .crc-card-type__main {
        font-size: 6px;
        font-weight: 800;
        letter-spacing: 0.05em;
        line-height: 1.15;
    }

    .member-card--crc-vale .crc-card-type__sub {
        font-size: 4.5px;
        color: var(--crc-blue);
        margin-top: 0.35mm;
        letter-spacing: 0.04em;
        font-weight: 600;
    }

    .member-card--crc-vale .crc-label {
        display: block;
        font-size: 3.6px;
        letter-spacing: 0.1em;
        color: rgba(255,255,255,.55);
        margin-bottom: 0.15mm;
    }

    .member-card--crc-vale .crc-value {
        display: block;
        font-size: 6.5px;
        font-weight: 700;
        line-height: 1.1;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .member-card--crc-vale .crc-value--name {
        font-size: 8.5px;
        font-weight: 800;
        margin-bottom: 0.7mm;
    }

    .member-card--crc-vale .crc-meta {
        display: flex;
        gap: 0;
        margin-bottom: 0.5mm;
    }

    .member-card--crc-vale .crc-meta__col {
        flex: 1;
        min-width: 0;
        padding-right: 1.2mm;
    }

    .member-card--crc-vale .crc-meta__col + .crc-meta__col {
        border-left: 0.2mm solid rgba(255,255,255,.25);
        padding-left: 1.2mm;
        padding-right: 0;
    }

    .member-card--crc-vale .crc-bottom {
        display: grid;
        grid-template-columns: 10mm 1fr auto;
        gap: 0.8mm 1mm;
        align-items: end;
        margin-top: auto;
        padding-top: 0.4mm;
    }

    .member-card--crc-vale .crc-qr {
        width: 10mm;
        height: 10mm;
        background: #fff;
        padding: 0.35mm;
        border-radius: 0.5mm;
        grid-row: span 2;
        align-self: end;
    }

    .member-card--crc-vale .crc-qr img {
        width: 100%;
        height: 100%;
        display: block;
    }

    .member-card--crc-vale .crc-scan {
        margin: 0;
        font-size: 3px;
        line-height: 1.35;
        color: rgba(255,255,255,.65);
        display: flex;
        gap: 0.5mm;
        align-items: flex-start;
        text-transform: uppercase;
    }

    .member-card--crc-vale .crc-scan svg {
        flex-shrink: 0;
        margin-top: 0.15mm;
        opacity: .75;
    }

    .member-card--crc-vale .crc-validade {
        display: inline-flex;
        align-items: center;
        gap: 0.7mm;
        padding: 0.45mm 0.9mm;
        border: 0.25mm solid rgba(255,255,255,.35);
        border-radius: 1mm;
        background: rgba(255,255,255,.06);
        justify-self: end;
    }

    .member-card--crc-vale .crc-validade__text {
        font-size: 3.6px;
        line-height: 1.2;
        color: rgba(255,255,255,.88);
    }

    .member-card--crc-vale .crc-validade__text strong {
        display: block;
        font-size: 5.2px;
        letter-spacing: 0.04em;
        margin-top: 0.1mm;
    }

    .member-card--crc-vale .crc-slogan {
        margin-top: 0.45mm;
        padding-top: 0.35mm;
        border-top: 0.2mm solid rgba(255,255,255,.12);
        text-align: center;
        line-height: 1.2;
    }

    .member-card--crc-vale .crc-slogan em {
        display: block;
        font-family: "Segoe Script", "Brush Script MT", "Snell Roundhand", cursive;
        font-style: italic;
        font-size: 5px;
        color: rgba(255,255,255,.92);
    }

    .member-card--crc-vale .crc-slogan span {
        font-size: 3.2px;
        color: var(--crc-blue);
        letter-spacing: 0.08em;
    }

    .member-card--crc-vale .crc-right {
        position: absolute;
        top: 0;
        right: 0;
        bottom: 0;
        width: 40%;
        z-index: 1;
        overflow: hidden;
    }

    .member-card--crc-vale .crc-crest {
        position: absolute;
        inset: 0;
        right: 2.6mm;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 12mm 0.5mm 14mm 1.5mm;
    }

    .member-card--crc-vale .crc-crest img {
        width: 100%;
        height: 100%;
        max-width: none;
        max-height: none;
        object-fit: contain;
        object-position: center;
        filter: drop-shadow(0 0.5px 1.5px rgba(10, 31, 68, .12));
    }

    .member-card--crc-vale .crc-crest__fallback {
        font-size: 7px;
        font-weight: 800;
        color: var(--crc-navy);
        text-align: center;
        line-height: 1.2;
        padding: 2mm;
    }

    .member-card--crc-vale .crc-right-foot {
        position: absolute;
        left: 0;
        right: 2.6mm;
        bottom: 0.8mm;
        z-index: 2;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.7mm;
    }

    .member-card--crc-vale .crc-badge {
        font-size: 4.8px;
        font-weight: 800;
        letter-spacing: 0.1em;
        padding: 0.65mm 2.8mm;
        border-radius: 1.2mm;
        flex-shrink: 0;
    }

    .member-card--crc-vale .crc-badge.is-active {
        background: var(--crc-navy);
        color: #fff;
    }

    .member-card--crc-vale .crc-badge.is-inactive {
        background: #64748b;
        color: #fff;
    }

    .member-card--crc-vale .crc-barcode {
        text-align: center;
        width: 100%;
    }

    .member-card--crc-vale .crc-barcode__bars {
        width: 19mm;
        height: 4mm;
        margin: 0 auto;
        padding: 0.3mm 0.5mm;
        border-radius: 0.3mm;
        background-color: #fff;
        background-image: repeating-linear-gradient(
            90deg,
            #0a1f44 0, #0a1f44 0.35mm,
            transparent 0.35mm, transparent 0.65mm,
            #0a1f44 0.65mm, #0a1f44 0.95mm,
            transparent 0.95mm, transparent 1.35mm
        );
    }

    .member-card--crc-vale .crc-barcode__num {
        display: block;
        margin-top: 0.3mm;
        font-size: 4.4px;
        font-family: ui-monospace, Consolas, monospace;
        font-weight: 700;
        letter-spacing: 0.14em;
        color: var(--crc-navy);
    }

    .member-card--crc-vale .crc-verified {
        display: flex;
        flex-direction: row;
        align-items: center;
        gap: 0.5mm;
        font-size: 2.8px;
        font-weight: 700;
        color: var(--crc-navy);
        line-height: 1;
    }

    .member-card--crc-vale .crc-verified__icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 3.8mm;
        height: 3.8mm;
        border-radius: 50%;
        background: var(--crc-navy);
        color: #fff;
        font-size: 3.5px;
    }

    .member-card--crc-vale .crc-holo {
        position: absolute;
        top: 0;
        right: 0;
        width: 2.6mm;
        height: 100%;
        z-index: 4;
        background: linear-gradient(
            180deg,
            rgba(180,210,255,.55),
            rgba(255,180,210,.45),
            rgba(180,255,210,.45),
            rgba(210,180,255,.45),
            rgba(180,210,255,.55)
        );
        opacity: .7;
    }
</style>
