import type { ClubBranding } from '../types';

export const MEMBER_AREA_MODULE = 'area_socio';

export function isMemberAreaEnabled(branding: ClubBranding): boolean {
  if (!branding.modules) {
    return true;
  }

  return branding.modules[MEMBER_AREA_MODULE] !== false;
}

export function memberAreaDisabledMessage(branding: ClubBranding): string {
  return (
    branding.member_area_disabled_message ??
    'A área do sócio não está disponível neste clube. Contacte a secretaria se precisar de ajuda.'
  );
}

export function arePasskeysEnabled(branding: ClubBranding): boolean {
  return branding.passkeys_enabled !== false;
}
