import { api } from './client';
import type { ClubBranding } from '../types';

export async function getBranding(): Promise<ClubBranding> {
  const { data } = await api.get<ClubBranding>('/branding');
  return data;
}
