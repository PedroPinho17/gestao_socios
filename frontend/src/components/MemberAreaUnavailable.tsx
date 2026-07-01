import { useEffect } from 'react';
import { clearStoredToken } from '../api/client';
import { useBranding } from '../branding/BrandingProvider';
import { memberAreaDisabledMessage } from '../branding/memberArea';
import { ClubBrandHeader } from './ClubBrandHeader';

export function MemberAreaUnavailable() {
  const { branding } = useBranding();

  useEffect(() => {
    clearStoredToken();
  }, []);

  return (
    <div className="login-page">
      <div className="login-page__glow" aria-hidden />

      <div className="card login-card member-area-unavailable">
        <ClubBrandHeader />

        <div className="login-card__body member-area-unavailable__body">
          <div className="member-area-unavailable__icon" aria-hidden>
            <svg viewBox="0 0 24 24" width="32" height="32" fill="none" stroke="currentColor" strokeWidth="1.75">
              <rect x="3" y="11" width="18" height="11" rx="2" />
              <path d="M7 11V7a5 5 0 0 1 10 0v4" />
            </svg>
          </div>

          <h2 className="member-area-unavailable__title">Sem acesso</h2>
          <p className="member-area-unavailable__text">{memberAreaDisabledMessage(branding)}</p>
        </div>
      </div>
    </div>
  );
}
