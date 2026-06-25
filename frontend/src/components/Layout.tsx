import { NavLink, Outlet } from 'react-router-dom';
import { useAuth } from '../auth/AuthContext';
import { useBranding } from '../branding/BrandingProvider';
import { ClubLogo } from './ClubLogo';

export function Layout() {
  const { profile, logout } = useAuth();
  const { branding } = useBranding();

  return (
    <div className="app-shell">
      <header className="app-header card app-header-card">
        <div className="app-header__brand">
          <ClubLogo size="sm" />
          <div>
            <p className="eyebrow">{branding.member_area_title}</p>
            <h1>{profile?.nome ?? 'Sócio'}</h1>
            {profile?.numero && <p className="muted">N.º {profile.numero}</p>}
          </div>
        </div>
        <button type="button" className="btn btn-ghost" onClick={() => logout()}>
          Sair
        </button>
      </header>

      <nav className="app-nav">
        <NavLink to="/area-socio" end className={({ isActive }) => (isActive ? 'active' : '')}>
          Início
        </NavLink>
        <NavLink to="/area-socio/pagamentos" className={({ isActive }) => (isActive ? 'active' : '')}>
          Pagamentos
        </NavLink>
      </nav>

      <main className="app-main">
        <Outlet />
      </main>

      <footer className="app-footer muted">{branding.club_name}</footer>
    </div>
  );
}
