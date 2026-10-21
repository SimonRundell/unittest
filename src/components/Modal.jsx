/**
 * Simple blocking modal used for login rejection messages (wrong password,
 * account locked, email not yet validated). Deliberately distinct from the
 * toast notifications used elsewhere, per the test grid.
 *
 * @param {{ title: string, message: string, onClose: () => void, actionLabel?: string, onAction?: () => void }} props
 */
export default function Modal({ title, message, onClose, actionLabel, onAction }) {
  return (
    <div className="modal-overlay" role="presentation" onClick={onClose}>
      <div
        className="modal-box"
        role="alertdialog"
        aria-modal="true"
        aria-labelledby="modal-title"
        onClick={(event) => event.stopPropagation()}
      >
        <h2 id="modal-title">{title}</h2>
        <p>{message}</p>
        <div className="modal-actions">
          {actionLabel && onAction && (
            <button type="button" className="button secondary" onClick={onAction}>
              {actionLabel}
            </button>
          )}
          <button type="button" className="button primary" onClick={onClose} autoFocus>
            OK
          </button>
        </div>
      </div>
    </div>
  );
}
