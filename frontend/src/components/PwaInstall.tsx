import '@khmyznikov/pwa-install';
import { useEffect, useRef } from 'react';
import type { PWAInstallElement } from '@khmyznikov/pwa-install';
import { useBranding } from '../branding/BrandingProvider';

export function PwaInstall() {
  const ref = useRef<PWAInstallElement>(null);
  const { branding } = useBranding();

  useEffect(() => {
    const element = ref.current;
    if (!element) {
      return;
    }

    if (window.promptEvent) {
      element.externalPromptEvent = window.promptEvent;
    }

    element.name = branding.member_area_title;
    element.description = branding.club_name;
    element.styles = { '--tint-color': branding.primary_color };
  }, [branding]);

  return <pwa-install ref={ref} manifest-url="/site.webmanifest" use-local-storage />;
}
