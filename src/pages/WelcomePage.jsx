import { useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';

export default function WelcomePage() {
  const { email, logout } = useAuth();
  const navigate = useNavigate();

  const handleLogout = async () => {
    await logout();
    navigate('/login');
  };

  return (
    <div className="auth-page">
      <div className="auth-card">
        <h1>Welcome</h1>
        <p>You are logged in as {email}.</p>
        <button type="button" className="button primary" onClick={handleLogout}>
          Logout
        </button>
      </div>
    </div>
  );
}
