<?php
/**
 * Database connection helper. Reads credentials from the (git-ignored)
 * api/.config.json file - never hardcode credentials in endpoint code.
 */

/**
 * @return array<string,mixed>
 */
function loadConfig(): array
{
    static $config = null;

    if ($config === null) {
        $path = __DIR__ . '/.config.json';
        $json = file_get_contents($path);
        $config = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
    }

    return $config;
}

function getPdo(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $cfg = loadConfig();
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
            $cfg['servername'],
            $cfg['port'],
            $cfg['dbname']
        );

        $pdo = new PDO($dsn, $cfg['username'], $cfg['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }

    return $pdo;
}

/**
 * Sends a JSON response and ends the script. Every endpoint should exit
 * through here so the response shape is always consistent.
 *
 * @param array<string,mixed> $body
 */
function sendJson(array $body, int $statusCode = 200): never
{
    http_response_code($statusCode);
    echo json_encode($body, JSON_UNESCAPED_SLASHES);
    exit();
}
