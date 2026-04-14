-- SQL script for external verification storage used by mod_customcert.
-- Adjust database name, credentials, and host as needed.

CREATE DATABASE IF NOT EXISTS external_customcert_verification
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE external_customcert_verification;

CREATE TABLE IF NOT EXISTS customcert_verifications (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    full_name VARCHAR(255) NOT NULL,
    username VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    issued_at DATETIME NOT NULL,
    code VARCHAR(64) NOT NULL,
    course_id BIGINT UNSIGNED NOT NULL,
    course_name VARCHAR(255) NOT NULL,
    site_url VARCHAR(255) NOT NULL,
    duration_minutes INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_customcert_code (code),
    KEY idx_username (username),
    KEY idx_course_id (course_id),
    KEY idx_email (email),
    KEY idx_issued_at (issued_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
