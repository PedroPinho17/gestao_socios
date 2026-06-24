@php
    use App\Support\MemberCardDimensions;
    use App\Support\MemberCardLayout;

    $withBleed = $withBleed ?? false;
    $forExport = $forExport ?? false;
    $widthMm = MemberCardDimensions::widthMm($withBleed);
    $heightMm = MemberCardDimensions::heightMm($withBleed);
    $bleedMm = $withBleed ? MemberCardDimensions::BLEED_MM : 0;
    $font = MemberCardLayout::fontStack($layout['font_family'] ?? 'system');
    $photoRadius = match ($layout['photo_shape'] ?? 'rounded') {
        'circle' => '50%',
        'square' => '0',
        default => '4px',
    };
    $borderStyle = ($layout['show_border'] ?? false)
        ? ($layout['border_width'] ?? 1).'px solid '.($layout['border_color'] ?? '#fff')
        : 'none';
@endphp
<style>
    .member-card-root {
        --card-w: {{ $widthMm }}mm;
        --card-h: {{ $heightMm }}mm;
        --card-bleed: {{ $bleedMm }}mm;
        --gradient-from: {{ $layout['gradient_from'] }};
        --gradient-to: {{ $layout['gradient_to'] }};
        --accent: {{ $layout['accent_color'] }};
        --text: {{ $layout['text_color'] ?? '#ffffff' }};
        --font: {!! $font !!};
        --photo-radius: {{ $photoRadius }};
        --border: {{ $borderStyle }};
        font-family: var(--font);
        box-sizing: border-box;
    }
    .member-card-root *, .member-card-root *::before, .member-card-root *::after {
        box-sizing: border-box;
    }
    .member-card-canvas {
        width: var(--card-w);
        height: var(--card-h);
        padding: var(--card-bleed);
        @if ($forExport)
        margin: 0;
        @endif
    }
    .member-card {
        width: {{ MemberCardDimensions::WIDTH_MM }}mm;
        height: {{ MemberCardDimensions::HEIGHT_MM }}mm;
        border-radius: {{ $forExport ? '0' : '3.5mm' }};
        overflow: hidden;
        color: var(--text);
        background: linear-gradient(135deg, var(--gradient-from), var(--gradient-to));
        border: var(--border);
        position: relative;
        @if (! $forExport)
        box-shadow: 0 10px 25px rgba(0,0,0,.15);
        @endif
    }
    .member-card-footer {
        font-size: 7px;
        text-align: center;
        color: var(--accent);
        opacity: .9;
        padding: 1.5mm 2mm 0;
        line-height: 1.3;
    }
    .member-card-label {
        font-size: 7px;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: var(--accent);
        font-weight: 700;
    }
    .member-card-name {
        font-size: 13px;
        font-weight: 700;
        line-height: 1.15;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .member-card-extra,
    .member-card-meta,
    .member-card-plano {
        font-size: 8px;
        color: var(--accent);
        opacity: .95;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .member-card-num {
        font-family: ui-monospace, Consolas, monospace;
        font-size: 11px;
        color: var(--accent);
        font-weight: 600;
        margin-top: 1mm;
    }
    .member-card-due {
        font-size: 7px;
        font-weight: 700;
        color: var(--accent);
        margin-top: 1mm;
    }
    .member-card-logo img {
        max-height: 11mm;
        max-width: 100%;
        object-fit: contain;
    }
    .member-card-logo-text {
        font-size: 8px;
        text-align: center;
        color: var(--accent);
        line-height: 1.2;
    }
    .member-card-photo {
        overflow: hidden;
        background: rgba(255,255,255,.12);
        border-radius: var(--photo-radius);
    }
    .member-card-photo img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }
    .member-card-photo-empty {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100%;
        font-size: 7px;
        color: rgba(255,255,255,.45);
    }
</style>
