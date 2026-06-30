import { useState } from 'react';
import { downloadPaymentReceipt } from '../api/member';
import { extractErrorMessage } from '../api/client';
import type { Payment } from '../types';

interface Props {
  payments: Payment[];
}

function PdfIcon() {
  return (
    <svg
      xmlns="http://www.w3.org/2000/svg"
      viewBox="0 0 24 24"
      fill="none"
      stroke="currentColor"
      strokeWidth="1.75"
      strokeLinecap="round"
      strokeLinejoin="round"
      aria-hidden="true"
      className="payment-receipt-btn__icon"
    >
      <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
      <polyline points="14 2 14 8 20 8" />
      <line x1="12" y1="18" x2="12" y2="12" />
      <polyline points="9 15 12 18 15 15" />
    </svg>
  );
}

export function PaymentList({ payments }: Props) {
  const [downloadingId, setDownloadingId] = useState<number | null>(null);
  const [error, setError] = useState<string | null>(null);

  if (payments.length === 0) {
    return <p className="empty-state">Ainda não há pagamentos registados.</p>;
  }

  async function handleDownload(payment: Payment) {
    setError(null);
    setDownloadingId(payment.id);
    try {
      await downloadPaymentReceipt(payment.id);
    } catch (err) {
      setError(extractErrorMessage(err));
    } finally {
      setDownloadingId(null);
    }
  }

  return (
    <div className="payment-list">
      {error && <div className="alert alert-error">{error}</div>}

      <ul className="payment-list__items">
        {payments.map((payment) => {
          const isDownloading = downloadingId === payment.id;

          return (
            <li key={payment.id} className="payment-card">
              <div className="payment-card__main">
                <div className="payment-card__date">{payment.data_formatted}</div>
                <div className="payment-card__valor">{payment.valor_formatted}</div>
                {payment.referencia && (
                  <div className="payment-card__ref">Ref. {payment.referencia}</div>
                )}
                {payment.notas && (
                  <p className="payment-card__notas">{payment.notas}</p>
                )}
              </div>

              <button
                type="button"
                className="btn btn-receipt"
                disabled={isDownloading}
                aria-label={`Descarregar comprovativo PDF do pagamento de ${payment.data_formatted}`}
                onClick={() => handleDownload(payment)}
              >
                <PdfIcon />
                <span>{isDownloading ? 'A preparar PDF…' : 'Comprovativo PDF'}</span>
              </button>
            </li>
          );
        })}
      </ul>
    </div>
  );
}
