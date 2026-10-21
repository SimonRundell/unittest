import axios from 'axios';

/**
 * Shared axios instance. Base URL comes from Vite env files so the exact
 * same code talks to http://localhost/api in dev and
 * https://unittest.toolsforteaching.co.uk/api in production - no proxy
 * needed, cors.php handles the cross-origin request from the Vite dev
 * server on port 5173.
 */
const apiClient = axios.create({
  baseURL: import.meta.env.VITE_API_BASE,
});

/**
 * Attaches the bearer token (if present) to every outgoing request.
 * @param {() => string | null} getToken
 */
export function attachAuthInterceptor(getToken) {
  apiClient.interceptors.request.use((config) => {
    const token = getToken();
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  });
}

export default apiClient;
