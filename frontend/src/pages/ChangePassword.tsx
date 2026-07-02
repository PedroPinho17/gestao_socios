import { type FormEvent, useState } from 'react';
import { Navigate, useNavigate } from 'react-router-dom';
import { extractErrorMessage, setStoredToken } from '../api/client';
import { changePassword } from '../api/member';
import { useAuth } from '../auth/AuthContext';
import { useBranding } from '../branding/BrandingProvider';
import { ClubBrandHeader } from '../components/ClubBrandHeader';
import { PasswordInput } from '../components/PasswordInput';

export function ChangePasswordPage() {
  const { profile, isLoading, logout, refreshProfile } = useAuth();
  const { branding } = useBranding();
  const navigate = useNavigate();
  const [password, setPassword] = useState('');
  const [passwordConfirmation, setPasswordConfirmation] = useState('');
  const [error, setError] = useState<string | null>(null);
  const [submitting, setSubmitting] = useState(false);

  if (isLoading) {
    return (
      <div className="page-center">
        <p className="muted">A carregar…</p>
      </div>
    );
  }

  if (!profile) {
    return <Navigate to="/login" replace />;
  }

  if (!profile.must_change_password) {
    return <Navigate to="/area-socio" replace />;
  }

  async function handleSubmit(event: FormEvent) {
    event.preventDefault();
    setError(null);
    setSubmitting(true);

    try {
      const { token } = await changePassword(password, passwordConfirmation);
      setStoredToken(token);
      await refreshProfile();
      navigate('/area-socio', { replace: true });
    } catch (err) {
      setError(extractErrorMessage(err));
    } finally {
      setSubmitting(false);
    }
  }

  return (
    <div className="login-page">
      <div className="login-page__glow" aria-hidden />

      <form className="card login-card" onSubmit={handleSubmit}>
        <ClubBrandHeader />

        <div className="login-card__body">
          <h2 className="change-password-title">Alterar password</h2>
          <p className="muted">
            Por segurança, deve definir uma nova password antes de aceder à {branding.member_area_title.toLowerCase()}.
          </p>

          {error && <div className="alert alert-error">{error}</div>}

          <label className="field" htmlFor="new-password">
            <span>Nova password</span>
            <PasswordInput
              id="new-password"
              autoComplete="new-password"
              required
              value={password}
              onChange={setPassword}
            />
          </label>

          <label className="field" htmlFor="confirm-password">
            <span>Confirmar nova password</span>
            <PasswordInput
              id="confirm-password"
              autoComplete="new-password"
              required
              value={passwordConfirmation}
              onChange={setPasswordConfirmation}
            />
          </label>

          <p className="field-hint">
            Mínimo 12 caracteres, com maiúsculas, minúsculas, números e símbolos.
          </p>

          <button type="submit" className="btn btn-primary" disabled={submitting}>
            {submitting ? 'A guardar…' : 'Guardar e continuar'}
          </button>

          <button
            type="button"
            className="btn btn-ghost change-password-logout"
            onClick={() => logout()}
          >
            Terminar sessão
          </button>
        </div>
      </form>
    </div>
  );
}
