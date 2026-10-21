import { useState } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import { toast } from 'sonner';
import apiClient from '../api/client';
import { COUNTRIES, LOCALES, DEFAULT_COUNTRY, DEFAULT_LOCALE } from '../data/locales';

export default function RegisterPage() {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');
  const [country, setCountry] = useState(DEFAULT_COUNTRY);
  const [locale, setLocale] = useState(DEFAULT_LOCALE);
  const [submitting, setSubmitting] = useState(false);
  const navigate = useNavigate();

  const handleSubmit = async (event) => {
    event.preventDefault();

    if (password !== confirmPassword) {
      toast.error('Passwords do not match.');
      return;
    }

    setSubmitting(true);
    try {
      const { data } = await apiClient.post('/register.php', {
        email,
        password,
        country,
        locale,
      });

      if (data.accountCreated) {
        toast.success('Account created.');
      }
      if (data.emailSent) {
        toast.success('Verification email sent.');
      } else {
        toast.error('Account created, but the verification email could not be sent.');
      }

      navigate('/login');
    } catch (error) {
      const payload = error.response?.data;
      toast.error(payload?.message ?? 'Registration failed. Please try again.');
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <div className="auth-page">
      <form className="auth-card" onSubmit={handleSubmit}>
        <h1>Register</h1>

        <label htmlFor="reg-email">Email</label>
        <input
          id="reg-email"
          type="email"
          autoComplete="username"
          value={email}
          onChange={(event) => setEmail(event.target.value)}
          required
        />

        <label htmlFor="reg-password">Password</label>
        <input
          id="reg-password"
          type="password"
          autoComplete="new-password"
          value={password}
          onChange={(event) => setPassword(event.target.value)}
          minLength={4}
          required
        />

        <label htmlFor="reg-confirm">Confirm password</label>
        <input
          id="reg-confirm"
          type="password"
          autoComplete="new-password"
          value={confirmPassword}
          onChange={(event) => setConfirmPassword(event.target.value)}
          minLength={4}
          required
        />

        <label htmlFor="reg-country">Country</label>
        <select id="reg-country" value={country} onChange={(event) => setCountry(event.target.value)}>
          {COUNTRIES.map((c) => (
            <option key={c.code} value={c.code}>
              {c.name}
            </option>
          ))}
        </select>

        <label htmlFor="reg-locale">Language / locale</label>
        <select id="reg-locale" value={locale} onChange={(event) => setLocale(event.target.value)}>
          {LOCALES.map((l) => (
            <option key={l.code} value={l.code}>
              {l.label}
            </option>
          ))}
        </select>

        <button type="submit" className="button primary" disabled={submitting}>
          {submitting ? 'Registering…' : 'Register'}
        </button>

        <p className="auth-switch">
          Already have an account? <Link to="/login">Log in</Link>
        </p>
      </form>
    </div>
  );
}
