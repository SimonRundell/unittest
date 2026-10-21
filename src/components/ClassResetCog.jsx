import { useState } from 'react';
import { toast } from 'sonner';
import apiClient from '../api/client';

/**
 * Small, deliberately unobtrusive teacher control: clears out every
 * registered account (except the fixed test@test.com account) that was
 * created before today, so the database is clean for a new class.
 * Gated behind a PIN (see api/.config.json -> adminResetPin) since this is
 * a real destructive action, not the decoy security example elsewhere in
 * this app.
 */
export default function ClassResetCog() {
  const [open, setOpen] = useState(false);
  const [pin, setPin] = useState('');
  const [submitting, setSubmitting] = useState(false);

  const close = () => {
    setOpen(false);
    setPin('');
  };

  const handleSubmit = async (event) => {
    event.preventDefault();
    setSubmitting(true);
    try {
      const { data } = await apiClient.post('/reset-class.php', { pin });
      toast.success(
        data.deleted > 0
          ? `Removed ${data.deleted} account(s) from before today.`
          : 'Nothing to clean up - no old accounts found.'
      );
      close();
    } catch (error) {
      toast.error(error.response?.data?.message ?? 'Reset failed.');
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <div className="class-reset-cog">
      <button
        type="button"
        className="class-reset-cog__trigger"
        aria-label="Reset class data"
        title="Reset class data"
        onClick={() => setOpen((value) => !value)}
      >
        <svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true">
          <path
            fill="currentColor"
            d="M19.14 12.94a7.07 7.07 0 0 0 .06-.94 7.07 7.07 0 0 0-.06-.94l2.03-1.58a.5.5 0 0 0 .12-.64l-1.92-3.32a.5.5 0 0 0-.6-.22l-2.39.96a7.3 7.3 0 0 0-1.62-.94l-.36-2.54a.5.5 0 0 0-.5-.42h-3.84a.5.5 0 0 0-.5.42l-.36 2.54c-.59.24-1.13.56-1.62.94l-2.39-.96a.5.5 0 0 0-.6.22L2.71 8.84a.5.5 0 0 0 .12.64l2.03 1.58a7.07 7.07 0 0 0-.06.94c0 .32.02.63.06.94l-2.03 1.58a.5.5 0 0 0-.12.64l1.92 3.32c.14.24.42.32.6.22l2.39-.96c.49.38 1.03.7 1.62.94l.36 2.54c.05.24.26.42.5.42h3.84c.24 0 .45-.18.5-.42l.36-2.54c.59-.24 1.13-.56 1.62-.94l2.39.96c.18.1.46.02.6-.22l1.92-3.32a.5.5 0 0 0-.12-.64zM12 15.5a3.5 3.5 0 1 1 0-7 3.5 3.5 0 0 1 0 7"
          />
        </svg>
      </button>

      {open && (
        <form className="class-reset-cog__popover" onSubmit={handleSubmit}>
          <p>Clear accounts created before today (keeps test@test.com).</p>
          <input
            type="password"
            placeholder="PIN"
            value={pin}
            onChange={(event) => setPin(event.target.value)}
            autoFocus
            required
          />
          <div className="class-reset-cog__actions">
            <button type="button" className="button secondary" onClick={close}>
              Cancel
            </button>
            <button type="submit" className="button primary" disabled={submitting}>
              {submitting ? 'Resetting…' : 'Reset'}
            </button>
          </div>
        </form>
      )}
    </div>
  );
}
