import {
  createContext,
  useCallback,
  useContext,
  useEffect,
  useMemo,
  useState,
  type ReactNode,
} from 'react';
import { clearStoredToken, getStoredToken, setStoredToken } from '../api/client';
import * as memberApi from '../api/member';
import * as webauthnApi from '../api/webauthn';
import { signWithPasskey } from '../lib/webauthn';
import type { MemberProfile } from '../types';

interface AuthContextValue {
  token: string | null;
  profile: MemberProfile | null;
  isLoading: boolean;
  login: (email: string, password: string) => Promise<void>;
  loginWithPasskey: (email: string) => Promise<void>;
  logout: () => Promise<void>;
  refreshProfile: () => Promise<void>;
}

const AuthContext = createContext<AuthContextValue | null>(null);

export function AuthProvider({ children }: { children: ReactNode }) {
  const [token, setToken] = useState<string | null>(() => getStoredToken());
  const [profile, setProfile] = useState<MemberProfile | null>(null);
  const [isLoading, setIsLoading] = useState(!!getStoredToken());

  const refreshProfile = useCallback(async () => {
    const data = await memberApi.getMe();
    setProfile(data);
  }, []);

  useEffect(() => {
    if (!token) {
      setIsLoading(false);
      return;
    }

    let cancelled = false;

    (async () => {
      try {
        await refreshProfile();
      } catch {
        clearStoredToken();
        setToken(null);
        setProfile(null);
      } finally {
        if (!cancelled) setIsLoading(false);
      }
    })();

    return () => {
      cancelled = true;
    };
  }, [token, refreshProfile]);

  const login = useCallback(async (email: string, password: string) => {
    const response = await memberApi.login(email, password);
    setStoredToken(response.token);
    setToken(response.token);
    setProfile({
      nome: response.user.nome,
      numero: response.user.numero,
      email,
      must_change_password: response.user.must_change_password,
      plano: null,
    });
    await refreshProfile();
  }, [refreshProfile]);

  const loginWithPasskey = useCallback(async (email: string) => {
    const { publicKey } = await webauthnApi.loginOptions(email);
    const credential = await signWithPasskey(publicKey);
    const response = await webauthnApi.loginWithPasskey(credential);
    setStoredToken(response.token);
    setToken(response.token);
    setProfile({
      nome: response.user.nome,
      numero: response.user.numero,
      email,
      must_change_password: response.user.must_change_password,
      plano: null,
    });
    await refreshProfile();
  }, [refreshProfile]);

  const logout = useCallback(async () => {
    try {
      if (token) await memberApi.logout();
    } catch {
      // limpar sessão local mesmo se a API falhar
    } finally {
      clearStoredToken();
      setToken(null);
      setProfile(null);
    }
  }, [token]);

  const value = useMemo(
    () => ({ token, profile, isLoading, login, loginWithPasskey, logout, refreshProfile }),
    [token, profile, isLoading, login, loginWithPasskey, logout, refreshProfile],
  );

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

export function useAuth(): AuthContextValue {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error('useAuth deve ser usado dentro de AuthProvider');
  }
  return context;
}
