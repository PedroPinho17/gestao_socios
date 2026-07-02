<script>
  window.addEventListener('beforeinstallprompt', (event) => {
    event.preventDefault();
    event.stopPropagation();
    event.stopImmediatePropagation();
    window.promptEvent = event;

    const element = document.querySelector('pwa-install');
    if (element) {
      element.externalPromptEvent = event;
    }
  });
</script>
