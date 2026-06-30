<div class="card-preview-shell">
    @php
        use App\Support\MemberCardLayout;

        $template = $layout['template'] ?? 'classic';
        if (! view()->exists('cards.templates.'.$template)) {
            $template = 'classic';
        }
        $hasVerso = MemberCardLayout::hasVerso($layout);
        $versoTemplate = MemberCardLayout::versoTemplate($layout);
    @endphp
    <div class="card-preview-row">
        <div class="card-preview-side">
            <p class="card-preview-side-label">Frente</p>
            <div class="card-preview-scale">
                @include('cards.templates.'.$template)
            </div>
        </div>
        @if ($hasVerso)
            <div class="card-preview-side">
                <p class="card-preview-side-label">Verso</p>
                <div class="card-preview-scale">
                    @include('cards.templates.'.$versoTemplate)
                </div>
            </div>
        @endif
    </div>
    <p class="card-preview-meta">
        Pré-visualização (CR80 {{ \App\Support\MemberCardDimensions::WIDTH_MM }}×{{ \App\Support\MemberCardDimensions::HEIGHT_MM }} mm)
        · modelo {{ MemberCardLayout::templateOptions()[$template] ?? $template }}
        @if ($hasVerso)
            · frente + verso
            @if ($layout['show_qr_verso'] ?? false)
                · QR
            @endif
        @endif
    </p>
</div>

<style>
    .card-preview-shell {
        margin-top: 1rem;
        padding: 1.25rem;
        border-radius: .75rem;
        border: 1px solid rgba(148,163,184,.35);
        background: linear-gradient(180deg, #f8fafc 0%, #e2e8f0 100%);
    }
    .card-preview-row {
        display: flex;
        flex-wrap: wrap;
        gap: 1.5rem;
        justify-content: center;
    }
    .card-preview-side-label {
        margin: 0 0 .5rem;
        text-align: center;
        font-size: .7rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: #64748b;
    }
    .card-preview-scale {
        display: flex;
        justify-content: center;
        transform-origin: top center;
    }
    .card-preview-meta {
        margin: .75rem 0 0;
        text-align: center;
        font-size: .75rem;
        color: #64748b;
    }
</style>
