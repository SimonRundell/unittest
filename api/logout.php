<?php
require_once __DIR__ . '/cors.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJson(['error' => 'method_not_allowed'], 405);
}

$token = bearerToken();
if ($token !== null) {
    $pdo = getPdo();
    $pdo->prepare('DELETE FROM sessions WHERE token = :token')->execute(['token' => $token]);
}

sendJson(['success' => true]);
