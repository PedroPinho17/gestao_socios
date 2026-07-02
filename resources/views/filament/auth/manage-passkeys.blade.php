<x-filament-panels::page>
    @once
        <script src="{{ asset('vendor/webauthn/webauthn.js') }}"></script>
    @endonce

    <div class="space-y-4">
        <p class="text-sm text-gray-600 dark:text-gray-300">
            Registe uma passkey (Touch ID, Windows Hello, chave de segurança) para entrar no painel sem password.
        </p>

        @if ($this->getPasskeys() === [])
            <p class="text-sm text-gray-500">Ainda não tem passkeys registadas.</p>
        @else
            <div class="divide-y rounded-xl border border-gray-200 dark:border-white/10">
                @foreach ($this->getPasskeys() as $key)
                    <div class="flex items-center justify-between gap-4 px-4 py-3">
                        <div>
                            <p class="font-medium text-gray-950 dark:text-white">{{ $key->name }}</p>
                            <p class="text-xs text-gray-500">Registada em {{ $key->created_at?->format('d/m/Y H:i') }}</p>
                        </div>
                        <x-filament::button
                            color="danger"
                            size="sm"
                            wire:click="deletePasskey({{ $key->id }})"
                            wire:confirm="Remover esta passkey?"
                        >
                            Remover
                        </x-filament::button>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('register-passkey', async ({ name }) => {
                if (typeof WebAuthn === 'undefined') {
                    alert('O browser não suporta WebAuthn nesta página.');
                    return;
                }

                try {
                    const component = Livewire.first();
                    const publicKey = await component.$wire.attestationOptions();
                    const webauthn = new WebAuthn();

                    webauthn.register(publicKey, async (data) => {
                        await component.$wire.completeRegistration(name, data);
                    });
                } catch (error) {
                    alert(error?.message || 'Não foi possível registar a passkey.');
                }
            });
        });
    </script>
</x-filament-panels::page>
