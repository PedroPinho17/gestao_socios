export type WebauthnCredential = {
  id: string;
  rawId: string;
  type: string;
  response: Record<string, string>;
};

type WebAuthnClient = {
  sign: (publicKey: unknown, callback: (data: WebauthnCredential) => void) => void;
  register: (publicKey: unknown, callback: (data: WebauthnCredential) => void) => void;
};

type WebAuthnConstructor = new (notifyCallback?: (name: string, message: string) => void) => WebAuthnClient;

declare global {
  interface Window {
    WebAuthn?: WebAuthnConstructor;
  }
}

export function appBaseUrl(): string {
  const apiUrl = import.meta.env.VITE_API_URL as string;
  return apiUrl.replace(/\/api\/?$/, '');
}

export async function loadWebAuthnClient(): Promise<WebAuthnClient> {
  let Constructor: WebAuthnConstructor | undefined = window.WebAuthn;

  if (!Constructor) {
    await new Promise<void>((resolve, reject) => {
      const script = document.createElement('script');
      script.src = `${appBaseUrl()}/vendor/webauthn/webauthn.js`;
      script.async = true;
      script.onload = () => resolve();
      script.onerror = () => reject(new Error('Não foi possível carregar o suporte WebAuthn.'));
      document.head.appendChild(script);
    });

    Constructor = window.WebAuthn;
  }

  if (!Constructor) {
    throw new Error('WebAuthn não está disponível neste browser.');
  }

  return new Constructor((name: string, message: string) => {
    throw new Error(message || name);
  });
}

export function signWithPasskey(publicKey: unknown): Promise<WebauthnCredential> {
  return new Promise(async (resolve, reject) => {
    try {
      const client = await loadWebAuthnClient();
      client.sign(publicKey, (data) => resolve(data));
    } catch (error) {
      reject(error);
    }
  });
}

export function registerPasskey(publicKey: unknown): Promise<WebauthnCredential> {
  return new Promise(async (resolve, reject) => {
    try {
      const client = await loadWebAuthnClient();
      client.register(publicKey, (data) => resolve(data));
    } catch (error) {
      reject(error);
    }
  });
}
