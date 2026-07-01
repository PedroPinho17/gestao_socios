<x-filament-panels::page>
    {{ $this->content }}

    @if (\App\Support\ModuleRegistry::enabled(\App\Support\ModuleRegistry::CARTOES))
        <div wire:key="card-preview-{{ md5(json_encode($this->data['card_layout'] ?? [])) }}">
            @include('cards.preview-shell', $this->getCardPreviewData())
        </div>
    @endif
</x-filament-panels::page>
