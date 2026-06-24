<div class="member-card-root">
    @include('cards.partials._styles')
    <div class="member-card-canvas">
        <div class="member-card member-card--minimal">
            <div class="member-card-minimal-inner">
                <div class="member-card-minimal-header">
                    @include('cards.partials._logo')
                    @if ($layout['show_foto'] ?? true)
                        @include('cards.partials._photo')
                    @endif
                </div>
                <div class="member-card-minimal-body">
                    @include('cards.partials._member-fields')
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .member-card--minimal {
        background: linear-gradient(160deg, var(--gradient-from) 0%, var(--gradient-to) 70%) !important;
    }
    .member-card--minimal .member-card-minimal-inner {
        height: 100%;
        padding: 3mm;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .member-card--minimal .member-card-minimal-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 2mm;
    }
    .member-card--minimal .member-card-logo img {
        max-height: 8mm;
    }
    .member-card--minimal .member-card-logo-text {
        text-align: left;
        font-weight: 600;
    }
    .member-card--minimal .member-card-photo {
        width: 10mm;
        height: 10mm;
    }
    .member-card--minimal .member-card-label {
        letter-spacing: .14em;
        font-size: 6px;
    }
    .member-card--minimal .member-card-name {
        font-size: 12px;
        margin-top: .5mm;
    }
    .member-card--minimal .member-card-num {
        font-size: 10px;
        margin-top: 2mm;
    }
</style>
