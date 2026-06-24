<div class="member-card-root">
    @include('cards.partials._styles')
    <div class="member-card-canvas">
        <div class="member-card member-card--modern">
            <div class="member-card-modern-bar"></div>
            <div class="member-card-modern-inner">
                <div class="member-card-modern-top">
                    @include('cards.partials._logo')
                </div>
                <div class="member-card-modern-main">
                    @if (($layout['photo_position'] ?? 'right') === 'left')
                        @include('cards.partials._photo')
                    @endif
                    <div class="member-card-modern-body">
                        @include('cards.partials._member-fields')
                    </div>
                    @if (($layout['photo_position'] ?? 'right') !== 'left')
                        @include('cards.partials._photo')
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .member-card--modern .member-card-modern-bar {
        height: 4mm;
        background: var(--accent);
        opacity: .85;
    }
    .member-card--modern .member-card-modern-inner {
        height: calc(100% - 4mm);
        padding: 2mm 2.5mm;
        display: flex;
        flex-direction: column;
    }
    .member-card--modern .member-card-modern-top {
        display: flex;
        justify-content: {{ match ($layout['logo_position'] ?? 'left') {
            'center' => 'center',
            'right' => 'flex-end',
            default => 'flex-start',
        } }};
        margin-bottom: 1.5mm;
        min-height: 9mm;
    }
    .member-card--modern .member-card-modern-top .member-card-logo img {
        max-height: 8mm;
    }
    .member-card--modern .member-card-modern-main {
        display: flex;
        flex: 1;
        align-items: center;
        gap: 2mm;
        min-height: 0;
    }
    .member-card--modern .member-card-modern-body {
        flex: 1;
        min-width: 0;
    }
    .member-card--modern .member-card-photo {
        width: 13mm;
        height: 13mm;
        flex-shrink: 0;
    }
    .member-card--modern .member-card-name {
        font-size: 12px;
    }
</style>
