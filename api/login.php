<?php
require_once __DIR__ . '/cors.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJson(['error' => 'method_not_allowed'], 405);
}

const MAX_FAILED_ATTEMPTS = 3;

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$email = trim((string) ($input['email'] ?? ''));
$password = (string) ($input['password'] ?? '');

$genericError = ['error' => 'invalid_credentials', 'message' => 'Email or password is incorrect.'];

if ($email === '' || $password === '') {
    sendJson($genericError, 401);
}

$pdo = getPdo();
$stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email');
$stmt->execute(['email' => $email]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password_hash'])) {
    // Wrong password (or unknown account): count the failed attempt and
    // lock after MAX_FAILED_ATTEMPTS. NOTE (QA training bug, left in on
    // purpose): the lock is recorded here but only ever checked below in
    // the *correct*-password branch, so a run of wrong-password attempts
    // never actually surfaces the "account locked" message - it just keeps
    // returning the generic invalid-credentials error. See the test grid
    // row "Account blocked after 3 unsuccessful attempts".
    if ($user) {
        $failedAttempts = $user['failed_attempts'] + 1;
        $isLocked = $failedAttempts >= MAX_FAILED_ATTEMPTS ? 1 : 0;
        $update = $pdo->prepare(
            'UPDATE users SET failed_attempts = :attempts, is_locked = :locked WHERE id = :id'
        );
        $update->execute(['attempts' => $failedAttempts, 'locked' => $isLocked, 'id' => $user['id']]);
    }

    sendJson($genericError, 401);
}

if ($user['is_locked']) {
    sendJson([
        'error' => 'account_locked',
        'message' => 'Your account has been locked after too many failed login attempts.',
    ], 403);
}

if (!$user['is_validated']) {
    sendJson([
        'error' => 'not_validated',
        'message' => 'Please verify your email address before logging in.',
    ], 403);
}

$pdo->prepare('UPDATE users SET failed_attempts = 0 WHERE id = :id')->execute(['id' => $user['id']]);

$token = createSession($pdo, (int) $user['id']);

sendJson([
    'success' => true,
    'token' => $token,
    'email' => $user['email'],
]);
