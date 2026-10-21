<?php
/**
 * Shared CORS handling, included first by every endpoint in this API.
 *
 * Reflects the request Origin back when it is localhost on any port (Vite
 * dev server) or the production domain, so the same code works unmodified
 * in both environments without a Vite proxy.
 */

$allowedOrigins = [
    'https://unittest.toolsforteaching.co.uk',
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$isLocalhost = (bool) preg_match('#^https?://localhost(:\d+)?$#i', $origin);

if ($origin !== '' && ($isLocalhost || in_array($origin, $allowedOrigins, true))) {
    header("Access-Control-Allow-Origin: $origin");
}

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Authorization, Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
