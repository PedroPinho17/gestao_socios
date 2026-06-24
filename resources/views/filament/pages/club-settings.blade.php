<x-filament-panels::page>
    {{ $this->content }}

    <div wire:key="card-preview-{{ md5(json_encode($this->data['card_layout'] ?? [])) }}">
        @include('cards.preview-shell', $this->getCardPreviewData())
    </div>
</x-filament-panels::page>
