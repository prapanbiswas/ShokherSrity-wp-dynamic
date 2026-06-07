<?php
define('DB_NAME',     'wordpress');
define('DB_USER',     'root');
define('DB_PASSWORD', '');
define('DB_HOST',     'localhost');
define('DB_CHARSET',  'utf8mb4');
define('DB_COLLATE',  'utf8mb4_unicode_ci');

// ── Security Keys (strong, non-patterned) ──────────────────────
define('AUTH_KEY',         'x7!mQ#vB2pL@nR5sT8wY1zA3cE6fH9jK0dG4iN*uX^oC~eW(qF+hP&yV-bD%lU');
define('SECURE_AUTH_KEY',  'kR3@sN7!mX2pQ5wL8vT1zA4cY6fB9jH0dE#uG*oI^nW(eC~qF+hK&yM-bP%lV)');
define('LOGGED_IN_KEY',    'pM9!nL2@xQ7vR4sT1wY8zA5cB3fH6jE0dG#uK*oI^nW(eC~qF+hK&yM-bP%lV)');
define('NONCE_KEY',        'yB4@vM8!nQ3pL7sR2wT9zA6cX1fH5jE0dG#uK*oI^nW(eC~qF+hK&yM-bP%lVz');
define('AUTH_SALT',        'wT6!mQ1@vB8pL3nR7sY2zA9cE4fH0jK5dG#uX*oI^nC~eW(qF+hP&yV-bD%lU)s');
define('SECURE_AUTH_SALT', 'zQ5@sN2!mX8pR3wL7vT4zA1cY6fB9jH0dE#uG*oI^nC~eW(qF+hK&yM-bP%lV)k');
define('LOGGED_IN_SALT',   'nR8!vL4@xQ2pM7sT3wY1zA5cB9fH6jE0dG#uK*oI^nC~eW(qF+hK&yM-bP%lV)q');
define('NONCE_SALT',       'cB7@mQ4!nL1pX8sR2wT5zA3cY9fH6jE0dG#uK*oI^nC~eW(qF+hK&yM-bP%lV)r');

$table_prefix = 'wp_';

define('WP_DEBUG',         false);
define('WP_DEBUG_LOG',     false);
define('WP_DEBUG_DISPLAY', false);

// Disable theme/plugin file editing and auto-updates via admin
define('DISALLOW_FILE_EDIT', true);
define('DISALLOW_FILE_MODS', true);
define('WP_AUTO_UPDATE_CORE', false);

// ── Reverse-proxy awareness (Replit, Cloudflare, nginx) ────────
if (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https') {
    $_SERVER['HTTPS'] = 'on';
}

// ── Dynamic URL — works on localhost and any Replit domain ─────
if (!defined('WP_HOME')) {
    $ss_https  = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    $ss_scheme = $ss_https ? 'https' : 'http';
    $ss_host   = $_SERVER['HTTP_HOST'] ?? 'localhost:5000';
    define('WP_HOME',    $ss_scheme . '://' . $ss_host);
    define('WP_SITEURL', $ss_scheme . '://' . $ss_host);
}

if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}

require_once ABSPATH . 'wp-settings.php';
