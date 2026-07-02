@once
    <script src="{{ asset('vendor/webauthn/webauthn.js') }}"></script>
@endonce

<p id="staff-passkey-error" class="fi-passkey-error is-hidden" role="alert"></p>

<style>
    .fi-passkey-error {
        margin: 0.75rem 0 0;
        width: 100%;
        text-align: center;
        font-size: 0.875rem;
        line-height: 1.25rem;
        color: var(--danger-600, #dc2626);
    }

    .fi-passkey-error.is-hidden {
        display: none;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const button = document.getElementById('staff-passkey-login');
        const errorBox = document.getElementById('staff-passkey-error');
        if (!button || typeof WebAuthn === 'undefined') return;

        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

        const setLoading = (loading) => {
            button.disabled = loading;
            button.classList.toggle('fi-processing', loading);
            button.setAttribute('aria-busy', loading ? 'true' : 'false');
        };

        button.addEventListener('click', async (event) => {
            event.preventDefault();
            event.stopPropagation();

            errorBox.classList.add('is-hidden');
            errorBox.textContent = '';

            const emailInput = document.querySelector('input[name="email"], input[type="email"]');
            const email = emailInput?.value?.trim();
            if (!email) {
                errorBox.textContent = 'Indique o email antes de usar a passkey.';
                errorBox.classList.remove('is-hidden');
                emailInput?.focus();
                return;
            }

            setLoading(true);

            try {
                const optionsResponse = await fetch(@json(url('/webauthn/auth/options')), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ email }),
                });

                if (!optionsResponse.ok) {
                    throw new Error('Não foi possível iniciar a autenticação por passkey.');
                }

                const { publicKey } = await optionsResponse.json();
                const webauthn = new WebAuthn((name, message) => {
                    throw new Error(message || name);
                });

                await new Promise((resolve, reject) => {
                    webauthn.sign(publicKey, async (data) => {
                        try {
                            const authResponse = await fetch(@json(url('/webauthn/auth')), {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': csrf,
                                    'X-Requested-With': 'XMLHttpRequest',
                                },
                                credentials: 'same-origin',
                                body: JSON.stringify(data),
                            });

                            if (!authResponse.ok) {
                                throw new Error('Passkey recusada ou não registada para este utilizador.');
                            }

                            const payload = await authResponse.json();
                            window.location.href = payload.callback || @json(url('/admin'));
                            resolve(null);
                        } catch (error) {
                            reject(error);
                        }
                    });
                });
            } catch (error) {
                errorBox.textContent = error?.message || 'Falha na autenticação por passkey.';
                errorBox.classList.remove('is-hidden');
            } finally {
                setLoading(false);
            }
        });
    });
</script>
