import { useState } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import { toast } from 'sonner';
import apiClient from '../api/client';
import { useAuth } from '../context/AuthContext';
import Modal from '../components/Modal';
import ClassResetCog from '../components/ClassResetCog';

export default function LoginPage() {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [submitting, setSubmitting] = useState(false);
  const [rejection, setRejection] = useState(null);
  const { login } = useAuth();
  const navigate = useNavigate();

  const handleResend = async () => {
    setRejection(null);
    try {
      const { data } = await apiClient.post('/resend-validation.php', { email });
      toast.success(data.message);
    } catch {
      toast.error('Could not resend the verification email right now.');
    }
  };

  const handleSubmit = async (event) => {
    event.preventDefault();
    setSubmitting(true);
    try {
      const { data } = await apiClient.post('/login.php', { email, password });
      login(data.token, data.email);
      navigate('/welcome');
    } catch (error) {
      const payload = error.response?.data;
      if (payload?.error === 'not_validated') {
        setRejection({
          title: 'Email not verified',
          message: payload.message,
          offerResend: true,
        });
      } else if (payload?.error === 'account_locked') {
        setRejection({ title: 'Account locked', message: payload.message });
      } else {
        setRejection({
          title: 'Login failed',
          message: payload?.message ?? 'Email or password is incorrect.',
        });
      }
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <div className="auth-page">
      <form className="auth-card" onSubmit={handleSubmit}>
        <h1>Log in</h1>
        <label htmlFor="email">Email</label>
        <input
          id="email"
          type="email"
          autoComplete="username"
          value={email}
          onChange={(event) => setEmail(event.target.value)}
          required
        />

        <label htmlFor="password">Password</label>
        <input
          id="password"
          type="password"
          autoComplete="current-password"
          value={password}
          onChange={(event) => setPassword(event.target.value)}
          required
        />

        <button type="submit" className="button primary" disabled={submitting}>
          {submitting ? 'Logging in…' : 'Log in'}
        </button>

        <p className="auth-switch">
          No account yet? <Link to="/register">Register</Link>
        </p>
      </form>

      {rejection && (
        <Modal
          title={rejection.title}
          message={rejection.message}
          onClose={() => setRejection(null)}
          actionLabel={rejection.offerResend ? 'Resend verification email' : undefined}
          onAction={rejection.offerResend ? handleResend : undefined}
        />
      )}

      <ClassResetCog />
    </div>
  );
}
