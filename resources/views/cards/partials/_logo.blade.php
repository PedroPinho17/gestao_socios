<div class="member-card-logo">
    @if ($forExport && $logoDataUri)
        <img src="{{ $logoDataUri }}" alt="">
    @elseif (! $forExport && $settings->logoUrl())
        <img src="{{ $settings->logoUrl() }}" alt="">
    @else
        <div class="member-card-logo-text">{{ $layout['nome_clube'] }}</div>
    @endif
</div>
