<?php
/**
 * Teacher-only utility: wipes every registered account except the fixed
 * test@test.com account that was created before today, so the database is
 * clean for a new class. Gated behind a PIN (api/.config.json ->
 * adminResetPin) - this deletes real data, so it must not be callable by
 * anyone who just finds the button.
 */
require_once __DIR__ . '/cors.php';
require_once __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJson(['error' => 'method_not_allowed'], 405);
}

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$pin = (string) ($input['pin'] ?? '');

$cfg = loadConfig();
$expectedPin = (string) ($cfg['adminResetPin'] ?? '');

if ($expectedPin === '' || !hash_equals($expectedPin, $pin)) {
    sendJson(['error' => 'invalid_pin', 'message' => 'Incorrect PIN.'], 401);
}

$pdo = getPdo();
$stmt = $pdo->prepare(
    'DELETE FROM users WHERE id != :keep_id AND created_at < CURDATE()'
);
$stmt->execute(['keep_id' => 2]);

sendJson(['success' => true, 'deleted' => $stmt->rowCount()]);
