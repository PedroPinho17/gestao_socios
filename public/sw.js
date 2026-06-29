// Service worker de auto-limpeza.
// Este projeto não usa PWA/offline no backoffice. Um service worker antigo
// ficou registado em alguns browsers e intercetava a navegação (mostrando uma
// "página offline"). Este ficheiro substitui-o e desregista-se a si próprio,
// limpando também todas as caches, para repor o funcionamento normal.

self.addEventListener('install', () => {
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    (async () => {
      try {
        const keys = await caches.keys();
        await Promise.all(keys.map((key) => caches.delete(key)));
      } catch (e) {
        // ignora
      }

      await self.registration.unregister();

      const clients = await self.clients.matchAll({ type: 'window' });
      clients.forEach((client) => client.navigate(client.url));
    })(),
  );
});
