<?php
/**
 * Resets the fixed QA test account (test@test.com / 1234) to a known-good
 * state: validated, unlocked, zero failed attempts. Run this between test
 * passes so the "Login with correct data" and "Account blocked" grid rows
 * behave consistently however many times the suite has been run before.
 *
 * Usage: php database/seed.php
 */
require_once __DIR__ . '/../api/db.php';

$pdo = getPdo();

$email = 'test@test.com';
$passwordHash = password_hash('1234', PASSWORD_DEFAULT);

$stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email');
$stmt->execute(['email' => $email]);
$existing = $stmt->fetch();

if ($existing) {
    $pdo->prepare(
        'UPDATE users SET password_hash = :hash, is_validated = 1, failed_attempts = 0,
                is_locked = 0, validation_token = NULL, validation_token_expires = NULL
         WHERE id = :id'
    )->execute(['hash' => $passwordHash, 'id' => $existing['id']]);
    echo "Reset existing test@test.com account.\n";
} else {
    $pdo->prepare(
        'INSERT INTO users (email, password_hash, country_code, locale, is_validated)
         VALUES (:email, :hash, :country, :locale, 1)'
    )->execute(['email' => $email, 'hash' => $passwordHash, 'country' => 'US', 'locale' => 'en-US']);
    echo "Created test@test.com account.\n";
}

echo "Done.\n";
