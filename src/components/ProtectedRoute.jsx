import { useEffect, useState } from 'react';
import { Navigate } from 'react-router-dom';
import apiClient from '../api/client';
import { useAuth } from '../context/AuthContext';

/**
 * Guards a route: redirects to /login if there's no local session, and
 * also double-checks the token against the server (/me.php) so a stale or
 * tampered token in localStorage doesn't grant access either.
 */
export default function ProtectedRoute({ children }) {
  const { isAuthenticated, logout } = useAuth();
  const [checked, setChecked] = useState(false);
  const [valid, setValid] = useState(false);

  useEffect(() => {
    if (!isAuthenticated) {
      setChecked(true);
      return;
    }

    let cancelled = false;
    apiClient
      .get('/me.php')
      .then(() => {
        if (!cancelled) {
          setValid(true);
        }
      })
      .catch(() => {
        if (!cancelled) {
          logout();
        }
      })
      .finally(() => {
        if (!cancelled) {
          setChecked(true);
        }
      });

    return () => {
      cancelled = true;
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [isAuthenticated]);

  if (!isAuthenticated) {
    return <Navigate to="/login" replace />;
  }

  if (!checked) {
    return null;
  }

  return valid ? children : <Navigate to="/login" replace />;
}
