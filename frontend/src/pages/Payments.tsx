import { useEffect, useState } from 'react';
import { extractErrorMessage } from '../api/client';
import { getPayments } from '../api/member';
import { PaymentList } from '../components/PaymentList';
import type { Payment } from '../types';

export function PaymentsPage() {
  const [payments, setPayments] = useState<Payment[]>([]);
  const [error, setError] = useState<string | null>(null);
  const [loading, setLoading] = useState(true);
  const [page, setPage] = useState(1);
  const [lastPage, setLastPage] = useState(1);

  useEffect(() => {
    let cancelled = false;

    (async () => {
      setLoading(true);
      setError(null);

      try {
        const response = await getPayments(page);
        if (!cancelled) {
          setPayments(response.data);
          setLastPage(response.meta?.last_page ?? 1);
        }
      } catch (err) {
        if (!cancelled) setError(extractErrorMessage(err));
      } finally {
        if (!cancelled) setLoading(false);
      }
    })();

    return () => {
      cancelled = true;
    };
  }, [page]);

  if (loading) {
    return <p className="muted">A carregar pagamentos…</p>;
  }

  if (error) {
    return <div className="alert alert-error">{error}</div>;
  }

  return (
    <div className="stack">
      <section className="card">
        <h2>Histórico de pagamentos</h2>
        <p className="page-intro">
          Consulte os pagamentos registados pelo clube. Em cada um pode descarregar o comprovativo em PDF.
        </p>
        <PaymentList payments={payments} />
      </section>

      {lastPage > 1 && (
        <div className="pagination">
          <button
            type="button"
            className="btn btn-ghost"
            disabled={page <= 1}
            onClick={() => setPage((p) => p - 1)}
          >
            Anterior
          </button>
          <span className="muted">
            Página {page} de {lastPage}
          </span>
          <button
            type="button"
            className="btn btn-ghost"
            disabled={page >= lastPage}
            onClick={() => setPage((p) => p + 1)}
          >
            Seguinte
          </button>
        </div>
      )}
    </div>
  );
}
