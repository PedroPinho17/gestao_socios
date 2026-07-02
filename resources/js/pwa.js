import '@khmyznikov/pwa-install';

function attachPromptEvent() {
    const element = document.querySelector('pwa-install');

    if (element && window.promptEvent) {
        element.externalPromptEvent = window.promptEvent;
    }
}

function registerAdminServiceWorker() {
    if (!('serviceWorker' in navigator)) {
        return;
    }

    navigator.serviceWorker.register('/sw.js', { scope: '/' }).catch(() => {
        // Ignorar se o browser bloquear o registo.
    });
}

registerAdminServiceWorker();
attachPromptEvent();
document.addEventListener('DOMContentLoaded', attachPromptEvent);
