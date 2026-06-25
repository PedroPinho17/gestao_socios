import {
  createContext,
  useContext,
  useEffect,
  useMemo,
  useState,
  type ReactNode,
} from 'react';
import { getBranding } from '../api/branding';
import type { ClubBranding } from '../types';

const defaultBranding: ClubBranding = {
  club_name: 'O meu clube',
  logo_url: null,
  primary_color: '#0f766e',
  gradient_from: '#0f766e',
  gradient_to: '#0f172a',
  accent_color: '#d1fae5',
  member_area_title: 'Área do sócio',
  member_area_login_subtitle: 'Inicie sessão com o email e password do clube.',
};

interface BrandingContextValue {
  branding: ClubBranding;
  isLoading: boolean;
}

const BrandingContext = createContext<BrandingContextValue | null>(null);

function applyBrandingVariables(branding: ClubBranding): void {
  const root = document.documentElement;
  root.style.setProperty('--club-primary', branding.primary_color);
  root.style.setProperty('--club-gradient-from', branding.gradient_from);
  root.style.setProperty('--club-gradient-to', branding.gradient_to);
  root.style.setProperty('--club-accent', branding.accent_color);
  document.title = `${branding.member_area_title} — ${branding.club_name}`;
}

export function BrandingProvider({ children }: { children: ReactNode }) {
  const [branding, setBranding] = useState<ClubBranding>(defaultBranding);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    let cancelled = false;

    (async () => {
      try {
        const data = await getBranding();
        if (!cancelled) {
          setBranding(data);
          applyBrandingVariables(data);
        }
      } catch {
        if (!cancelled) applyBrandingVariables(defaultBranding);
      } finally {
        if (!cancelled) setIsLoading(false);
      }
    })();

    return () => {
      cancelled = true;
    };
  }, []);

  const value = useMemo(() => ({ branding, isLoading }), [branding, isLoading]);

  return <BrandingContext.Provider value={value}>{children}</BrandingContext.Provider>;
}

export function useBranding(): BrandingContextValue {
  const context = useContext(BrandingContext);
  if (!context) {
    throw new Error('useBranding deve ser usado dentro de BrandingProvider');
  }
  return context;
}
