-- Schema for the Unit Testing exercise app.
-- Run once against the target database (local Laragon MariaDB, or the
-- Hostinger MySQL database for the deployed site).

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    email VARCHAR(190) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    country_code CHAR(2) NOT NULL DEFAULT 'US',
    locale VARCHAR(10) NOT NULL DEFAULT 'en-US',
    is_validated TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
    validation_token VARCHAR(64) NULL,
    validation_token_expires DATETIME NULL,
    failed_attempts INT UNSIGNED NOT NULL DEFAULT 0,
    is_locked TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_email (email),
    KEY idx_users_validation_token (validation_token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS sessions (
    token CHAR(64) NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME NOT NULL,
    PRIMARY KEY (token),
    KEY idx_sessions_user_id (user_id),
    CONSTRAINT fk_sessions_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
