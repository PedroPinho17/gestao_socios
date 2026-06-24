@if ($layout['show_foto'] ?? true)
    <div class="member-card-photo">
        @if ($forExport && $fotoDataUri)
            <img src="{{ $fotoDataUri }}" alt="">
        @elseif (! $forExport && $member->fotoUrl())
            <img src="{{ $member->fotoUrl() }}" alt="">
        @else
            <div class="member-card-photo-empty">foto</div>
        @endif
    </div>
@endif
