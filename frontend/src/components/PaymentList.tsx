import type { Payment } from '../types';

interface Props {
  payments: Payment[];
}

export function PaymentList({ payments }: Props) {
  if (payments.length === 0) {
    return <p className="empty-state">Ainda não há pagamentos registados.</p>;
  }

  return (
    <div className="table-wrap">
      <table className="data-table">
        <thead>
          <tr>
            <th>Data</th>
            <th>Valor</th>
            <th>Notas</th>
          </tr>
        </thead>
        <tbody>
          {payments.map((payment) => (
            <tr key={payment.id}>
              <td>{payment.data_formatted}</td>
              <td>{payment.valor_formatted}</td>
              <td>{payment.notas ?? '—'}</td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}
