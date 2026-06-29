import { api } from './client';
import type {
  LoginResponse,
  MemberProfile,
  PaginatedPayments,
  QuotaStatusResponse,
} from '../types';

export async function login(email: string, password: string): Promise<LoginResponse> {
  const { data } = await api.post<LoginResponse>('/login', { email, password });
  return data;
}

export async function logout(): Promise<void> {
  await api.post('/logout');
}

export async function getMe(): Promise<MemberProfile> {
  const { data } = await api.get<MemberProfile>('/me');
  return data;
}

export async function changePassword(password: string, passwordConfirmation: string): Promise<void> {
  await api.put('/me/password', {
    password,
    password_confirmation: passwordConfirmation,
  });
}

export async function getQuota(): Promise<QuotaStatusResponse> {
  const { data } = await api.get<QuotaStatusResponse>('/me/quota');
  return data;
}

export async function getPayments(page = 1): Promise<PaginatedPayments> {
  const { data } = await api.get<PaginatedPayments>('/me/payments', {
    params: { page },
  });
  return data;
}

export async function downloadPaymentReceipt(paymentId: number): Promise<void> {
  const response = await api.get(`/me/payments/${paymentId}/receipt`, {
    responseType: 'blob',
  });

  const blob = new Blob([response.data], { type: 'application/pdf' });
  const url = window.URL.createObjectURL(blob);
  const link = document.createElement('a');
  link.href = url;
  link.download = `comprovativo_${paymentId}.pdf`;
  document.body.appendChild(link);
  link.click();
  link.remove();
  window.URL.revokeObjectURL(url);
}
