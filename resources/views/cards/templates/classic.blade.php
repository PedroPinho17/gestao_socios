<div class="member-card-root">
    @include('cards.partials._styles')
    <div class="member-card-canvas">
        <div class="member-card member-card--classic">
            <div class="member-card-classic-inner">
                @include('cards.partials._logo')
                <div class="member-card-classic-body">
                    @include('cards.partials._member-fields')
                </div>
                @include('cards.partials._photo')
            </div>
        </div>
    </div>
</div>

<style>
    .member-card--classic .member-card-classic-inner {
        display: flex;
        height: 100%;
        padding: 2.5mm;
        position: relative;
    }
    .member-card--classic .member-card-logo {
        width: 34%;
        flex-shrink: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        border-right: 1px solid {{ $layout['accent_color'] }}33;
        padding-right: 2mm;
    }
    .member-card--classic .member-card-classic-body {
        flex: 1;
        min-width: 0;
        padding: 0 2mm 0 2.5mm;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    .member-card--classic .member-card-photo {
        position: absolute;
        bottom: 2mm;
        right: 2mm;
        width: 11mm;
        height: 11mm;
    }
    .member-card--classic .member-card-classic-body {
        padding-right: 13mm;
    }
</style>
