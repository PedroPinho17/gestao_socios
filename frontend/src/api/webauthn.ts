import { api } from './client';
import type { LoginResponse } from '../types';
import type { WebauthnCredential } from '../lib/webauthn';

export async function loginOptions(email: string): Promise<{ publicKey: unknown }> {
  const { data } = await api.post<{ publicKey: unknown }>('/webauthn/login/options', { email });
  return data;
}

export async function loginWithPasskey(credential: WebauthnCredential): Promise<LoginResponse> {
  const { data } = await api.post<LoginResponse>('/webauthn/login', credential);
  return data;
}

export type PasskeySummary = {
  id: number;
  name: string;
  created_at: string;
};

export async function listPasskeys(): Promise<PasskeySummary[]> {
  const { data } = await api.get<{ keys: PasskeySummary[] }>('/webauthn/keys');
  return data.keys;
}

export async function registerOptions(): Promise<{ publicKey: unknown }> {
  const { data } = await api.post<{ publicKey: unknown }>('/webauthn/keys/options');
  return data;
}

export async function registerPasskey(name: string, credential: WebauthnCredential): Promise<void> {
  await api.post('/webauthn/keys', { name, ...credential });
}

export async function deletePasskey(id: number): Promise<void> {
  await api.delete(`/webauthn/keys/${id}`);
}
