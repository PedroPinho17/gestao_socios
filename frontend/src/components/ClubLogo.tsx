import { useBranding } from '../branding/BrandingProvider';

interface Props {
  size?: 'sm' | 'md' | 'lg';
  className?: string;
}

const sizeClass = {
  sm: 'club-logo-sm',
  md: 'club-logo-md',
  lg: 'club-logo-lg',
};

export function ClubLogo({ size = 'md', className = '' }: Props) {
  const { branding } = useBranding();

  if (branding.logo_url) {
    return (
      <img
        src={branding.logo_url}
        alt=""
        className={`club-logo ${sizeClass[size]} ${className}`.trim()}
      />
    );
  }

  const initials = branding.club_name
    .split(/\s+/)
    .filter(Boolean)
    .slice(0, 2)
    .map((word) => word[0]?.toUpperCase() ?? '')
    .join('');

  return (
    <span
      className={`club-logo-fallback ${sizeClass[size]} ${className}`.trim()}
      aria-hidden
    >
      {initials || 'C'}
    </span>
  );
}
