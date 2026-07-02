import { useEffect, useState } from 'react';
import { Navigate } from 'react-router-dom';
import { extractErrorMessage } from '../api/client';
import * as webauthnApi from '../api/webauthn';
import { useBranding } from '../branding/BrandingProvider';
import { arePasskeysEnabled } from '../branding/memberArea';
import { registerPasskey } from '../lib/webauthn';

export function PasskeysPage() {
  const { branding } = useBranding();
  const [keys, setKeys] = useState<webauthnApi.PasskeySummary[]>([]);
  const [loading, setLoading] = useState(true);
  const [registering, setRegistering] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [name, setName] = useState('');
  const passkeysEnabled = arePasskeysEnabled(branding);

  async function refresh() {
    setLoading(true);
    try {
      setKeys(await webauthnApi.listPasskeys());
    } catch (err) {
      setError(extractErrorMessage(err));
    } finally {
      setLoading(false);
    }
  }

  useEffect(() => {
    if (!passkeysEnabled) {
      return;
    }

    void refresh();
  }, [passkeysEnabled]);

  async function handleRegister(event: React.FormEvent) {
    event.preventDefault();
    if (!name.trim()) return;

    setError(null);
    setRegistering(true);

    try {
      const { publicKey } = await webauthnApi.registerOptions();
      const credential = await registerPasskey(publicKey);
      await webauthnApi.registerPasskey(name.trim(), credential);
      setName('');
      await refresh();
    } catch (err) {
      setError(extractErrorMessage(err));
    } finally {
      setRegistering(false);
    }
  }

  async function handleDelete(id: number) {
    setError(null);
    try {
      await webauthnApi.deletePasskey(id);
      await refresh();
    } catch (err) {
      setError(extractErrorMessage(err));
    }
  }

  if (!passkeysEnabled) {
    return <Navigate to="/area-socio" replace />;
  }

  return (
    <div className="stack">
      <section className="card">
        <h2>Passkeys</h2>
        <p className="muted">
          Registe o telemóvel ou portátil para entrar na área do sócio sem password.
        </p>

        {error && <div className="alert alert-error">{error}</div>}

        <form className="stack" onSubmit={handleRegister}>
          <label className="field" htmlFor="passkey-name">
            <span>Nome deste dispositivo</span>
            <input
              id="passkey-name"
              value={name}
              onChange={(e) => setName(e.target.value)}
              placeholder="Ex.: Telemóvel"
              maxLength={120}
            />
          </label>
          <button type="submit" className="btn btn-primary" disabled={registering}>
            {registering ? 'A registar…' : 'Registar passkey'}
          </button>
        </form>
      </section>

      <section className="card">
        <h3>Dispositivos registados</h3>
        {loading ? (
          <p className="muted">A carregar…</p>
        ) : keys.length === 0 ? (
          <p className="muted">Ainda não tem passkeys.</p>
        ) : (
          <ul className="passkey-list">
            {keys.map((key) => (
              <li key={key.id} className="passkey-list__item">
                <div>
                  <strong>{key.name}</strong>
                  <p className="muted">Registada em {new Date(key.created_at).toLocaleString('pt-PT')}</p>
                </div>
                <button type="button" className="btn btn-ghost" onClick={() => handleDelete(key.id)}>
                  Remover
                </button>
              </li>
            ))}
          </ul>
        )}
      </section>
    </div>
  );
}
