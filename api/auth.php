<?php
/**
 * Session token helpers. Sessions are opaque random tokens stored server
 * side (not JWTs) - simple to reason about and trivial to revoke on logout.
 */

const SESSION_LIFETIME_SECONDS = 7 * 24 * 60 * 60; // 7 days

function createSession(PDO $pdo, int $userId): string
{
    $token = bin2hex(random_bytes(32));
    $expiresAt = date('Y-m-d H:i:s', time() + SESSION_LIFETIME_SECONDS);

    $stmt = $pdo->prepare(
        'INSERT INTO sessions (token, user_id, expires_at) VALUES (:token, :user_id, :expires_at)'
    );
    $stmt->execute(['token' => $token, 'user_id' => $userId, 'expires_at' => $expiresAt]);

    return $token;
}

function bearerToken(): ?string
{
    $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if ($header === '' && function_exists('apache_request_headers')) {
        $headers = apache_request_headers();
        $header = $headers['Authorization'] ?? '';
    }

    if (preg_match('/^Bearer\s+(.+)$/i', $header, $matches)) {
        return $matches[1];
    }

    return null;
}

/**
 * Returns the authenticated user row for the current request, or null.
 *
 * @return array<string,mixed>|null
 */
function getAuthenticatedUser(PDO $pdo): ?array
{
    $token = bearerToken();
    if ($token === null) {
        return null;
    }

    $stmt = $pdo->prepare(
        'SELECT u.* FROM sessions s
         JOIN users u ON u.id = s.user_id
         WHERE s.token = :token AND s.expires_at > NOW()'
    );
    $stmt->execute(['token' => $token]);
    $user = $stmt->fetch();

    return $user ?: null;
}
