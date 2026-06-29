<x-filament-panels::page>
    {{ $this->content }}

    @if (! empty($whatsappLinks))
        <x-filament::section>
            <x-slot name="heading">
                Links de WhatsApp ({{ count($whatsappLinks) }})
            </x-slot>
            <x-slot name="description">
                Clique em cada sócio para abrir a conversa no WhatsApp com a mensagem já preenchida. Depois é só carregar em enviar.
            </x-slot>

            <div class="flex flex-col gap-2">
                @foreach ($whatsappLinks as $link)
                    <a
                        href="{{ $link['url'] }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="flex items-center justify-between gap-3 rounded-lg border border-gray-200 px-4 py-2 text-sm transition hover:bg-gray-50 dark:border-white/10 dark:hover:bg-white/5"
                    >
                        <span class="font-medium text-gray-950 dark:text-white">{{ $link['nome'] }}</span>
                        <span class="flex items-center gap-2 text-success-600 dark:text-success-400">
                            <span class="text-gray-500 dark:text-gray-400">{{ $link['telefone'] }}</span>
                            <x-filament::icon icon="heroicon-o-chat-bubble-left-right" class="h-5 w-5" />
                        </span>
                    </a>
                @endforeach
            </div>
        </x-filament::section>
    @endif
</x-filament-panels::page>
