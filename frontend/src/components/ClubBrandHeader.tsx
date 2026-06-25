import type { ReactNode } from 'react';
import { ClubLogo } from './ClubLogo';
import { useBranding } from '../branding/BrandingProvider';

interface Props {
  children?: ReactNode;
  compact?: boolean;
}

export function ClubBrandHeader({ children, compact = false }: Props) {
  const { branding } = useBranding();

  return (
    <header className={`club-brand-header${compact ? ' club-brand-header--compact' : ''}`}>
      <ClubLogo size={compact ? 'sm' : 'lg'} />
      <div className="club-brand-header__text">
        <h1>{branding.club_name}</h1>
        <p>{branding.member_area_title}</p>
      </div>
      {children}
    </header>
  );
}
