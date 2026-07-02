// Service worker mínimo para instalação PWA no backoffice.
// Sem cache offline — evita intercetar navegação do Filament (Livewire/AJAX).

self.addEventListener('install', () => {
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil(self.clients.claim());
});
