<x-filament-panels::page>
    {{ $this->content }}

    @if (\App\Support\FeatureRegistry::enabled('filament.cards'))
        @php($gallery = $this->getCardPreviewGallery())

        @if (count($gallery) > 1)
            <div class="mt-6 space-y-4" wire:key="card-gallery-{{ md5(json_encode($this->data['card_layout']['available_templates'] ?? [])) }}">
                <div>
                    <h3 class="text-base font-semibold text-gray-950 dark:text-white">Galeria de modelos activos</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Comparação dos modelos visíveis — útil para demonstrar opções base noutros clubes.
                    </p>
                </div>

                <div class="grid gap-6 xl:grid-cols-2">
                    @foreach ($gallery as $item)
                        <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900">
                            <p class="mb-3 text-center text-sm font-semibold text-gray-700 dark:text-gray-200">
                                {{ $item['label'] }}
                                @if (($this->data['card_layout']['template'] ?? null) === $item['template'])
                                    <span class="text-primary-600 dark:text-primary-400">· activo</span>
                                @endif
                            </p>
                            @include('cards.preview-shell', $item['data'])
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div wire:key="card-preview-{{ md5(json_encode($this->data['card_layout'] ?? [])) }}">
                @include('cards.preview-shell', $this->getCardPreviewData())
            </div>
        @endif
    @endif
</x-filament-panels::page>
