import { type ReactNode } from 'react';
import { useBranding } from './BrandingProvider';
import { isMemberAreaEnabled } from './memberArea';
import { MemberAreaUnavailable } from '../components/MemberAreaUnavailable';

export function MemberAreaGate({ children }: { children: ReactNode }) {
  const { branding, isLoading } = useBranding();

  if (isLoading) {
    return (
      <div className="page-center">
        <p className="muted">A carregar…</p>
      </div>
    );
  }

  if (!isMemberAreaEnabled(branding)) {
    return <MemberAreaUnavailable />;
  }

  return children;
}
