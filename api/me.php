<?php
require_once __DIR__ . '/cors.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendJson(['error' => 'method_not_allowed'], 405);
}

$pdo = getPdo();
$user = getAuthenticatedUser($pdo);

if (!$user) {
    sendJson(['error' => 'unauthenticated'], 401);
}

sendJson([
    'email' => $user['email'],
    'country' => $user['country_code'],
    'locale' => $user['locale'],
]);
