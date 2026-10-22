<?php
/**
 * Validation link landing page. Opened directly in a browser from the
 * verification email, so this renders a standalone HTML page rather than
 * JSON - there is no SPA involved in this one step.
 */
require_once __DIR__ . '/db.php';

$token = (string) ($_GET['token'] ?? '');
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$loginUrl = str_contains($host, 'localhost')
    ? 'http://localhost:5173/login'
    : 'https://unittest.toolsforteaching.co.uk/login';

function renderPage(string $title, string $message, string $loginUrl): never
{
    $title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
    $message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
    $loginUrl = htmlspecialchars($loginUrl, ENT_QUOTES, 'UTF-8');

    header('Content-Type: text/html; charset=utf-8');
    echo <<<HTML
    <!doctype html>
    <html lang="en">
    <head>
        <meta charset="utf-8">
        <title>{$title}</title>
        <style>
            body { font-family: "Trebuchet MS", Arial, sans-serif; background: #f4f6f8; color: #1a1a1a;
                   display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
            .card { background: #fff; border-radius: 10px; padding: 2.5rem; max-width: 420px;
                    box-shadow: 0 2px 12px rgba(0,0,0,0.08); text-align: center; }
            a.button { display: inline-block; margin-top: 1.25rem; padding: 0.6rem 1.4rem; background: #2563eb;
                       color: #fff; text-decoration: none; border-radius: 6px; }
        </style>
    </head>
    <body>
        <div class="card">
            <h1>{$title}</h1>
            <p>{$message}</p>
            <a class="button" href="{$loginUrl}">Go to login</a>
        </div>
    </body>
    </html>
    HTML;
    exit();
}

if ($token === '') {
    renderPage('Invalid link', 'This validation link is missing its token.', $loginUrl);
}

$pdo = getPdo();
$stmt = $pdo->prepare(
    'SELECT id, is_validated, validation_token_expires FROM users WHERE validation_token = :token'
);
$stmt->execute(['token' => $token]);
$user = $stmt->fetch();

if (!$user) {
    renderPage('Invalid link', 'This validation link is not recognised. It may have already been used.', $loginUrl);
}

if ($user['is_validated']) {
    renderPage('Already verified', 'This email address has already been validated. You can log in now.', $loginUrl);
}

if (strtotime($user['validation_token_expires']) < time()) {
    renderPage(
        'Link expired',
        'This validation link has expired. Please request a new one from the login screen.',
        $loginUrl
    );
}

// The token is deliberately kept after use. Mail scanners and link previews
// often open the link before the user does; if the token were cleared, the
// real click would show "not recognised" instead of "Already verified".
$pdo->prepare(
    'UPDATE users SET is_validated = 1 WHERE id = :id'
)->execute(['id' => $user['id']]);

renderPage('Email validated', 'Your email address has been validated. You can now log in.', $loginUrl);
