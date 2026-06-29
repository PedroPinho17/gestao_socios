import { useState } from 'react';
import { downloadPaymentReceipt } from '../api/member';
import { extractErrorMessage } from '../api/client';
import type { Payment } from '../types';

interface Props {
  payments: Payment[];
}

export function PaymentList({ payments }: Props) {
  const [downloadingId, setDownloadingId] = useState<number | null>(null);
  const [error, setError] = useState<string | null>(null);

  if (payments.length === 0) {
    return <p className="empty-state">Ainda não há pagamentos registados.</p>;
  }

  async function handleDownload(paymentId: number) {
    setError(null);
    setDownloadingId(paymentId);
    try {
      await downloadPaymentReceipt(paymentId);
    } catch (err) {
      setError(extractErrorMessage(err));
    } finally {
      setDownloadingId(null);
    }
  }

  return (
    <div className="table-wrap">
      {error && <div className="alert alert-error">{error}</div>}
      <table className="data-table">
        <thead>
          <tr>
            <th>Data</th>
            <th>Valor</th>
            <th>Notas</th>
            <th>Comprovativo</th>
          </tr>
        </thead>
        <tbody>
          {payments.map((payment) => (
            <tr key={payment.id}>
              <td>{payment.data_formatted}</td>
              <td>{payment.valor_formatted}</td>
              <td>{payment.notas ?? '—'}</td>
              <td>
                <button
                  type="button"
                  className="btn btn-ghost"
                  disabled={downloadingId === payment.id}
                  onClick={() => handleDownload(payment.id)}
                >
                  {downloadingId === payment.id ? 'A gerar…' : 'Descarregar'}
                </button>
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}
