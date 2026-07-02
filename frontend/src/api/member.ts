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

export async function changePassword(
  password: string,
  passwordConfirmation: string,
): Promise<{ token: string }> {
  const { data } = await api.put<{ message: string; token: string }>('/me/password', {
    password,
    password_confirmation: passwordConfirmation,
  });
  return { token: data.token };
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

function filenameFromContentDisposition(header: string | undefined): string | null {
  if (!header) return null;

  const utf8Match = /filename\*=UTF-8''([^;]+)/i.exec(header);
  if (utf8Match?.[1]) {
    return decodeURIComponent(utf8Match[1]);
  }

  const match = /filename="?([^";]+)"?/i.exec(header);
  return match?.[1] ?? null;
}

export async function downloadPaymentReceipt(paymentId: number): Promise<void> {
  const response = await api.get(`/me/payments/${paymentId}/receipt`, {
    responseType: 'blob',
  });

  const blob = new Blob([response.data], { type: 'application/pdf' });
  const url = window.URL.createObjectURL(blob);
  const link = document.createElement('a');
  link.href = url;
  link.download =
    filenameFromContentDisposition(response.headers['content-disposition']) ??
    `comprovativo_${paymentId}.pdf`;
  document.body.appendChild(link);
  link.click();
  link.remove();
  window.URL.revokeObjectURL(url);
}
