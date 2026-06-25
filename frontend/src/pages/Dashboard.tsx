import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { extractErrorMessage } from '../api/client';
import { getQuota } from '../api/member';
import { useAuth } from '../auth/AuthContext';
import { QuotaStatusBadge } from '../components/QuotaStatusBadge';
import type { QuotaStatusResponse } from '../types';

export function DashboardPage() {
  const { profile } = useAuth();
  const [quota, setQuota] = useState<QuotaStatusResponse | null>(null);
  const [error, setError] = useState<string | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    let cancelled = false;

    (async () => {
      try {
        const data = await getQuota();
        if (!cancelled) setQuota(data);
      } catch (err) {
        if (!cancelled) setError(extractErrorMessage(err));
      } finally {
        if (!cancelled) setLoading(false);
      }
    })();

    return () => {
      cancelled = true;
    };
  }, []);

  if (loading) {
    return <p className="muted">A carregar situação da quota…</p>;
  }

  if (error) {
    return <div className="alert alert-error">{error}</div>;
  }

  if (!quota) {
    return null;
  }

  return (
    <div className="stack">
      <section className="card">
        <h2>Situação da quota</h2>
        <div className="quota-header">
          <QuotaStatusBadge status={quota.status} label={quota.label} />
        </div>

        {quota.next_due_formatted && quota.next_due_formatted !== '—' && (
          <div className="highlight-box highlight-box--accent">
            <p className="eyebrow">Próximo vencimento</p>
            <p className="due-date">{quota.next_due_formatted}</p>
          </div>
        )}

        {quota.plan && (
          <dl className="meta-list">
            <div>
              <dt>Plano</dt>
              <dd>{quota.plan.nome}</dd>
            </div>
            <div>
              <dt>Valor</dt>
              <dd>
                {quota.plan.valor.toLocaleString('pt-PT', {
                  style: 'currency',
                  currency: 'EUR',
                })}
              </dd>
            </div>
            <div>
              <dt>Periodicidade</dt>
              <dd>{quota.plan.periodicidade}</dd>
            </div>
          </dl>
        )}
      </section>

      <section className="card">
        <h2>Conta</h2>
        <dl className="meta-list">
          <div>
            <dt>Nome</dt>
            <dd>{profile?.nome}</dd>
          </div>
          <div>
            <dt>N.º de sócio</dt>
            <dd>{profile?.numero}</dd>
          </div>
          <div>
            <dt>Email</dt>
            <dd>{profile?.email}</dd>
          </div>
        </dl>
        <Link to="/area-socio/pagamentos" className="btn btn-secondary">
          Ver histórico de pagamentos
        </Link>
      </section>
    </div>
  );
}
