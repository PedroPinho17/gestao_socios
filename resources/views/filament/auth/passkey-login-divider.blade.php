<div class="fi-passkey-divider" aria-hidden="true">
    <span>ou</span>
</div>

<style>
    .fi-passkey-divider {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.75rem;
        width: 100%;
        margin-top: 0.75rem;
        color: var(--gray-500, #9ca3af);
        font-size: 0.875rem;
        line-height: 1.25rem;
    }

    .fi-passkey-divider::before,
    .fi-passkey-divider::after {
        content: '';
        flex: 1 1 0%;
        height: 1px;
        background: color-mix(in srgb, var(--gray-950, #030712) 10%, transparent);
    }

    .dark .fi-passkey-divider::before,
    .dark .fi-passkey-divider::after {
        background: rgb(255 255 255 / 0.1);
    }
</style>
