<?php
require_once __DIR__ . '/cors.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/mailer.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJson(['error' => 'method_not_allowed'], 405);
}

$input = json_decode(file_get_contents('php://input'), true) ?? [];

$email = trim((string) ($input['email'] ?? ''));
$password = (string) ($input['password'] ?? '');
$country = strtoupper(trim((string) ($input['country'] ?? '')));
$locale = trim((string) ($input['locale'] ?? ''));

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendJson(['error' => 'invalid_email', 'message' => 'Please enter a valid email address.'], 422);
}

if (strlen($password) < 4) {
    sendJson(['error' => 'weak_password', 'message' => 'Password must be at least 4 characters.'], 422);
}

if (!preg_match('/^[A-Z]{2}$/', $country)) {
    $country = 'US';
}

if (!preg_match('/^[a-z]{2}-[A-Z]{2}$/', $locale)) {
    $locale = 'en-US';
}

$pdo = getPdo();

$stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email');
$stmt->execute(['email' => $email]);
if ($stmt->fetch()) {
    sendJson(['error' => 'email_taken', 'message' => 'That email address is already registered.'], 409);
}

$passwordHash = password_hash($password, PASSWORD_DEFAULT);
$token = bin2hex(random_bytes(32));
$tokenExpires = date('Y-m-d H:i:s', time() + 24 * 60 * 60);

$stmt = $pdo->prepare(
    'INSERT INTO users (email, password_hash, country_code, locale, validation_token, validation_token_expires)
     VALUES (:email, :password_hash, :country_code, :locale, :token, :token_expires)'
);
$stmt->execute([
    'email' => $email,
    'password_hash' => $passwordHash,
    'country_code' => $country,
    'locale' => $locale,
    'token' => $token,
    'token_expires' => $tokenExpires,
]);

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$validationLink = "{$scheme}://{$host}/api/validate.php?token={$token}";

$emailSent = true;
$emailError = null;
try {
    sendValidationEmail(loadConfig(), $email, $validationLink);
} catch (Throwable $e) {
    $emailSent = false;
    $emailError = $e->getMessage();
    error_log('Validation email send failed: ' . $emailError);
}

sendJson([
    'success' => true,
    'accountCreated' => true,
    'emailSent' => $emailSent,
]);
