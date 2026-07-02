import { registerSW } from 'virtual:pwa-register';

registerSW({
  immediate: true,
  onOfflineReady() {
    // Instalação PWA disponível; sem UI extra aqui (pwa-install trata o prompt).
  },
});
