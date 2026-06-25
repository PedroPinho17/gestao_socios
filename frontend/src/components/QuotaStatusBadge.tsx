import type { QuotaStatus } from '../types';

const statusClass: Record<QuotaStatus, string> = {
  ok: 'badge-ok',
  due_soon: 'badge-due-soon',
  overdue: 'badge-overdue',
  sem_plano: 'badge-neutral',
  inativo: 'badge-neutral',
};

const shortLabel: Record<QuotaStatus, string> = {
  ok: 'Em dia',
  due_soon: 'Vence em breve',
  overdue: 'Em atraso',
  sem_plano: 'Sem plano',
  inativo: 'Inativo',
};

interface Props {
  status: QuotaStatus;
  label?: string;
}

export function QuotaStatusBadge({ status, label }: Props) {
  return (
    <span className={`badge ${statusClass[status]}`}>
      {label ?? shortLabel[status]}
    </span>
  );
}
