<?php
require_once __DIR__ . '/cors.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/mailer.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJson(['error' => 'method_not_allowed'], 405);
}

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$email = trim((string) ($input['email'] ?? ''));

// Always return the same generic response, whether or not the email exists
// or is already validated - this avoids leaking which addresses are
// registered (user enumeration).
$genericResponse = [
    'success' => true,
    'message' => 'If that email is registered and not yet verified, a new verification link has been sent.',
];

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendJson($genericResponse);
}

$pdo = getPdo();
$stmt = $pdo->prepare('SELECT id, is_validated FROM users WHERE email = :email');
$stmt->execute(['email' => $email]);
$user = $stmt->fetch();

if ($user && !$user['is_validated']) {
    $token = bin2hex(random_bytes(32));
    $tokenExpires = date('Y-m-d H:i:s', time() + 24 * 60 * 60);

    $pdo->prepare(
        'UPDATE users SET validation_token = :token, validation_token_expires = :expires WHERE id = :id'
    )->execute(['token' => $token, 'expires' => $tokenExpires, 'id' => $user['id']]);

    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $validationLink = "{$scheme}://{$host}/api/validate.php?token={$token}";

    try {
        sendValidationEmail(loadConfig(), $email, $validationLink);
    } catch (Throwable $e) {
        error_log('Resend validation email failed: ' . $e->getMessage());
    }
}

sendJson($genericResponse);
