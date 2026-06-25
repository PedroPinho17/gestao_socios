import { Navigate, Outlet } from 'react-router-dom';
import { useAuth } from './AuthContext';

export function RequirePasswordChanged() {
  const { profile, isLoading } = useAuth();

  if (isLoading) {
    return (
      <div className="page-center">
        <p className="muted">A carregar…</p>
      </div>
    );
  }

  if (profile?.must_change_password) {
    return <Navigate to="/alterar-password" replace />;
  }

  return <Outlet />;
}
