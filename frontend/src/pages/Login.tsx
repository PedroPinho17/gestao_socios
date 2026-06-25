import { type FormEvent, useState } from 'react';
import { Navigate, useNavigate } from 'react-router-dom';
import { extractErrorMessage } from '../api/client';
import { useAuth } from '../auth/AuthContext';
import { useBranding } from '../branding/BrandingProvider';
import { ClubBrandHeader } from '../components/ClubBrandHeader';
import { PasswordInput } from '../components/PasswordInput';

export function LoginPage() {
  const { login, token } = useAuth();
  const { branding, isLoading: brandingLoading } = useBranding();
  const navigate = useNavigate();
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState<string | null>(null);
  const [loading, setLoading] = useState(false);

  if (token) {
    return <Navigate to="/area-socio" replace />;
  }

  async function handleSubmit(event: FormEvent) {
    event.preventDefault();
    setError(null);
    setLoading(true);

    try {
      await login(email, password);
      navigate('/area-socio');
    } catch (err) {
      setError(extractErrorMessage(err));
    } finally {
      setLoading(false);
    }
  }

  return (
    <div className="login-page">
      <div className="login-page__glow" aria-hidden />

      {brandingLoading ? (
        <div className="card login-card login-card--loading">
          <p className="muted">A carregar…</p>
        </div>
      ) : (
        <form className="card login-card" onSubmit={handleSubmit}>
          <ClubBrandHeader />

          <div className="login-card__body">
            <p className="muted">{branding.member_area_login_subtitle}</p>

            {error && <div className="alert alert-error">{error}</div>}

            <label className="field" htmlFor="login-email">
              <span>Email</span>
              <input
                id="login-email"
                type="email"
                autoComplete="email"
                required
                value={email}
                onChange={(e) => setEmail(e.target.value)}
              />
            </label>

            <label className="field" htmlFor="login-password">
              <span>Password</span>
              <PasswordInput
                id="login-password"
                autoComplete="current-password"
                required
                value={password}
                onChange={setPassword}
              />
            </label>

            <button type="submit" className="btn btn-primary" disabled={loading}>
              {loading ? 'A entrar…' : 'Entrar'}
            </button>
          </div>
        </form>
      )}
    </div>
  );
}
