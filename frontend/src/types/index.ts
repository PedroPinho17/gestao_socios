export interface ClubBranding {
  club_name: string;
  logo_url: string | null;
  primary_color: string;
  gradient_from: string;
  gradient_to: string;
  accent_color: string;
  member_area_title: string;
  member_area_login_subtitle: string;
  member_area_disabled_message?: string;
  modules?: Record<string, boolean>;
}

export type QuotaStatus = 'ok' | 'due_soon' | 'overdue' | 'sem_plano' | 'inativo';

export interface QuotaPlan {
  nome: string;
  valor: number;
  periodicidade: string;
}

export interface MemberProfile {
  nome: string;
  numero: string;
  email: string;
  must_change_password: boolean;
  plano: QuotaPlan | null;
}

export interface QuotaStatusResponse {
  status: QuotaStatus;
  label: string;
  next_due: string | null;
  next_due_formatted: string;
  days_overdue: number | null;
  days_until: number | null;
  plan: QuotaPlan | null;
}

export interface Payment {
  id: number;
  data: string;
  data_formatted: string;
  valor: number;
  valor_formatted: string;
  referencia: string;
  notas: string | null;
}

export interface PaginatedPayments {
  data: Payment[];
  meta?: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
}

export interface LoginResponse {
  token: string;
  user: {
    nome: string;
    numero: string;
    must_change_password: boolean;
  };
}
