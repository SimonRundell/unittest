import { createContext, useContext, useMemo, useState } from 'react';
import apiClient, { attachAuthInterceptor } from '../api/client';

const AuthContext = createContext(null);
const STORAGE_KEY = 'unittest.session';

/**
 * Holds the current session token/email, persisted to localStorage so a
 * page refresh doesn't log the user out. Also wires the axios interceptor
 * so every request automatically carries the bearer token.
 */
export function AuthProvider({ children }) {
  const [session, setSession] = useState(() => {
    const stored = localStorage.getItem(STORAGE_KEY);
    return stored ? JSON.parse(stored) : null;
  });

  attachAuthInterceptor(() => session?.token ?? null);

  const login = (token, email) => {
    const next = { token, email };
    localStorage.setItem(STORAGE_KEY, JSON.stringify(next));
    setSession(next);
  };

  const logout = async () => {
    try {
      await apiClient.post('/logout.php');
    } catch {
      // Already logged out server-side (expired/invalid token) - proceed
      // with clearing local state regardless.
    }
    localStorage.removeItem(STORAGE_KEY);
    setSession(null);
  };

  const value = useMemo(
    () => ({
      token: session?.token ?? null,
      email: session?.email ?? null,
      isAuthenticated: Boolean(session?.token),
      login,
      logout,
    }),
    [session]
  );

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

export function useAuth() {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error('useAuth must be used within an AuthProvider');
  }
  return context;
}
